import { createHash } from 'node:crypto';
import fsSync from 'node:fs';
import fs from 'node:fs/promises';
import os from 'node:os';
import path from 'node:path';

/**
 * Theme images: resources/images/pages/** → public/images/**, optimised at build time.
 *
 * Filenames and folders are kept exactly, because App\Support\Assets builds those URLs by hand
 * (Assets::url('home/hero.jpg')). Hashing them, as laravel-vite-plugin does for files it imports,
 * would break every call site. Every raster also gets a .webp sibling, which Assets::webp()
 * serves. All of this happens on the build machine: shared hosting never runs Node.
 *
 * Ported from Remote Leverage, where it handled ~630 source images.
 */

const SOURCE_DIR = 'resources/images/pages';
const OUT_DIR = 'public/images';
const CACHE_FILE = 'node_modules/.cache/theme-images.json';

const RASTER = new Set(['.png', '.jpg', '.jpeg']);
const PASSTHROUGH = new Set(['.gif', '.webp', '.avif']);
const SOURCES = new Set([...RASTER, ...PASSTHROUGH, '.svg']);

/**
 * Extra widths emitted next to a source, for an LCP image that is far larger than a phone needs:
 * 'home/hero.jpg': [{ width: 750, quality: 80 }] writes home/hero-750.webp. Part of the cache
 * key, so adding one rebuilds that source.
 *
 * @type {Record<string, Array<{ width: number, quality?: number }>>}
 */
const RESPONSIVE_VARIANTS = {};

// sharp and svgo are only needed when there is something to build; importing them lazily keeps
// `vite dev` from loading the native binding on every start.
async function loadTooling() {
  const [{ default: sharp }, { optimize }] = await Promise.all([import('sharp'), import('svgo')]);

  return { sharp, optimize };
}

function hash(buffer) {
  return createHash('sha1').update(buffer).digest('hex').slice(0, 16);
}

async function readCache() {
  try {
    return JSON.parse(await fs.readFile(CACHE_FILE, 'utf8'));
  } catch {
    return {};
  }
}

async function writeCache(cache) {
  await fs.mkdir(path.dirname(CACHE_FILE), { recursive: true });
  await fs.writeFile(CACHE_FILE, JSON.stringify(cache));
}

async function outputsExist(outputs) {
  const checks = await Promise.all(outputs.map((file) => fs.access(file).then(() => true, () => false)));

  return checks.every(Boolean);
}

async function write(file, contents) {
  await fs.mkdir(path.dirname(file), { recursive: true });
  await fs.writeFile(file, contents);
}

async function sources() {
  if (! fsSync.existsSync(SOURCE_DIR)) {
    return [];
  }

  const entries = await fs.readdir(SOURCE_DIR, { recursive: true });

  return entries
    .map((entry) => entry.split(path.sep).join('/'))
    .filter((entry) => SOURCES.has(path.extname(entry).toLowerCase()));
}

/**
 * Whether a sibling shares this file's stem with another raster extension, which would make
 * both write the same .webp (and one card silently show the other's picture).
 */
function hasStemCollision(relative) {
  const dir = path.dirname(relative);
  const ext = path.extname(relative).toLowerCase();
  const stem = path.basename(relative, path.extname(relative));

  return [...RASTER]
    .filter((candidate) => candidate !== ext)
    .some((candidate) => fsSync.existsSync(path.join(SOURCE_DIR, dir, `${stem}${candidate}`)));
}

async function writeVariants(sharp, input, relative) {
  const outputs = [];

  for (const variant of RESPONSIVE_VARIANTS[relative] ?? []) {
    const parsed = path.parse(relative);
    const dest = path.join(OUT_DIR, parsed.dir, `${parsed.name}-${variant.width}.webp`);
    const buffer = await sharp(input, { failOn: 'none' })
      .resize({ width: variant.width, withoutEnlargement: true })
      .webp({ quality: variant.quality ?? 80, effort: 5 })
      .toBuffer();

    await write(dest, buffer);
    outputs.push(dest);
  }

  return outputs;
}

async function processFile({ sharp, optimize }, relative) {
  const source = path.join(SOURCE_DIR, relative);
  const target = path.join(OUT_DIR, relative);
  const ext = path.extname(relative).toLowerCase();
  const input = await fs.readFile(source);

  if (ext === '.svg') {
    const { data } = optimize(input.toString('utf8'), {
      path: source,
      multipass: true,
      // Markup may target ids, and viewBox must survive for SVGs sized with CSS.
      plugins: [{ name: 'preset-default', params: { overrides: { cleanupIds: false, removeViewBox: false } } }],
    });

    await write(target, data);

    return [target];
  }

  if (PASSTHROUGH.has(ext)) {
    await write(target, input);

    return [target, ...(await writeVariants(sharp, input, relative))];
  }

  const pipeline = sharp(input, { failOn: 'none' });

  // The source raster is only the fallback (browsers get the .webp), so it is re-encoded
  // losslessly or not at all: re-compressing an already-lossy JPEG compounds its artifacts.
  const recompressed = ext === '.png'
    ? await pipeline.clone().png({ compressionLevel: 9, effort: 10, palette: false }).toBuffer()
    : input;

  // Never let "optimisation" grow a file.
  await write(target, recompressed.length < input.length ? recompressed : input);

  // PNG usually means flat colour, hard edges or transparency (logos, icons), which lossy WebP
  // shreds: encode it losslessly, and only fall back to lossy for photos saved as PNG, where
  // lossless comes out larger.
  const webpTarget = hasStemCollision(relative) ? `${target}.webp` : target.replace(/\.(png|jpe?g)$/i, '.webp');
  let webp = ext === '.png'
    ? await pipeline.clone().webp({ lossless: true, effort: 5 }).toBuffer()
    : await pipeline.clone().webp({ quality: 82, effort: 5 }).toBuffer();

  if (ext === '.png' && webp.length > recompressed.length) {
    webp = await pipeline.clone().webp({ quality: 90, effort: 5 }).toBuffer();
  }

  await write(webpTarget, webp);

  return [target, webpTarget, ...(await writeVariants(sharp, input, relative))];
}

export default function themeImages() {
  let root = process.cwd();

  return {
    name: 'alma:theme-images',

    configResolved(config) {
      root = config.root ?? root;
    },

    async buildStart() {
      const previous = process.cwd();
      process.chdir(root);

      try {
        const files = await sources();

        if (files.length === 0) {
          return;
        }

        const tooling = await loadTooling();
        const cache = await readCache();
        const next = {};
        let built = 0;

        // Lossless WebP is slow; sharp releases the event loop while encoding, so a small worker
        // pool scales the build with the machine.
        const queue = [...files];
        const workers = Array.from({ length: Math.min(queue.length, Math.max(1, os.cpus().length)) }, async () => {
          let relative;

          while ((relative = queue.shift()) !== undefined) {
            const input = await fs.readFile(path.join(SOURCE_DIR, relative));
            const digest = hash(Buffer.concat([input, Buffer.from('\0' + JSON.stringify(RESPONSIVE_VARIANTS[relative] ?? []))]));
            const cached = cache[relative];

            if (cached?.hash === digest && (await outputsExist(cached.outputs))) {
              next[relative] = cached;
              continue;
            }

            next[relative] = { hash: digest, outputs: await processFile(tooling, relative) };
            built++;
          }
        });

        await Promise.all(workers);
        await writeCache(next);

        this.info?.(`theme-images: ${files.length} sources, ${built} rebuilt, ${files.length - built} cached`);
      } finally {
        process.chdir(previous);
      }
    },
  };
}
