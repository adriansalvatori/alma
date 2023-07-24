import * as path from 'path'
import copy from 'rollup-plugin-copy'
import outputManifest from 'rollup-plugin-output-manifest'
import { defineConfig, loadEnv } from 'vite'

const publicDir = 'public'
const manifestFile = 'manifest.json'
const assets = {
    node_modules: 'node_modules/',
    base: 'resources',
    scripts: 'resources/scripts',
    styles: 'resources/styles',
    images: 'resources/images',
    fonts: 'resources/fonts'
}

const formatName = (name) => name.replace(`${assets.scripts}/`, '').replace(/.css$/gm, '')

export default defineConfig(({ mode }) => {
    const devServerConfig = loadEnv(mode, process.cwd(), 'HMR')
    const dev = mode === 'development'
    const config = {
        appType: 'custom',
        publicDir: false,
        base: './',
        resolve: {
            alias: {
                '@': path.resolve(__dirname, assets.base),
                '@scripts': path.resolve(__dirname, assets.scripts),
                '@styles': path.resolve(__dirname, assets.styles),
                '@fonts': path.resolve(__dirname, assets.fonts),
                '@images': path.resolve(__dirname, assets.images),
                '~': path.resolve(__dirname, assets.node_modules)
            
            }
        },
        css: {
            devSourcemap: true
        },
        build: {
            sourcemap: 'inline',
            manifest: false,
            outDir: publicDir,
            assetsDir: '',
            rollupOptions: {
                input: {
                    app: path.resolve(__dirname, `${assets.scripts}/app.js`),
                    editor: path.resolve(__dirname, `${assets.scripts}/editor.js`),
                },
                output: {
                    sourcemap: true
                },
                plugins: [
                    outputManifest({
                        fileName: manifestFile,
                        generate:
                            (keyValueDecorator, seed, opt) => chunks =>
                                chunks.reduce((manifest, { name, fileName }) => {
                                    return name
                                        ? {
                                              ...manifest,
                                              ...keyValueDecorator(formatName(name), fileName, opt)
                                          }
                                        : manifest
                                }, seed)
                    }),
                    outputManifest({
                        fileName: 'entrypoints.json',
                        nameWithExt: true,
                        generate: (_, seed) => chunks =>
                            chunks.reduce((manifest, { name, fileName }) => {
                                const formatedName = name && formatName(name)
                                const output = {}
                                const js =
                                    formatedName && manifest[formatedName]?.js?.length ? manifest[formatedName].js : []
                                const css =
                                    formatedName && manifest[formatedName]?.css?.length
                                        ? manifest[formatedName].css
                                        : []
                                const dependencies =
                                    formatedName && manifest[formatedName] ? manifest[formatedName].dependencies : []
                                const inject = { js, css, dependencies }

                                fileName.match(/.js$/gm) && js.push(fileName)
                                fileName.match(/.css$/gm) && css.push(fileName)

                                name && (output[formatedName] = inject)

                                return {
                                    ...manifest,
                                    ...output
                                }
                            }, seed)
                    }),
                    copy({
                        copyOnce: true,
                        hook: 'writeBundle',
                        targets: [
                            {
                                src: path.resolve(__dirname, `${assets.base}/images/**/*`),
                                dest: `${publicDir}/images`
                            },
                            {
                                src: path.resolve(__dirname, `${assets.base}/svg/**/*`),
                                dest: `${publicDir}/svg`
                            },
                            {
                                src: path.resolve(__dirname, `${assets.base}/fonts/**/*`),
                                dest: `${publicDir}/fonts`
                            }
                        ]
                    })
                ]
            }
        }
    }

    if (dev) {
        let host = 'localhost'
        let port = 5173
        const protocol = 'http'
        const https = !!(devServerConfig.HMR_HTTPS_KEY && devServerConfig.HMR_HTTPS_CERT)

        // unlink(`${publicDir}/${manifestFile}`, error =>
        //     console.log(`🧹 Wipe ${manifestFile} :`, error ? `No ${manifestFile} in the public directory` : '✅')
        // )

        devServerConfig.HMR_HOST && (host = devServerConfig.HMR_HOST)
        devServerConfig.HMR_PORT && (port = parseInt(devServerConfig.HMR_PORT))

        https &&
            (config.server.https = {
                key: devServerConfig.HMR_HTTPS_KEY,
                cert: devServerConfig.HMR_HTTPS_CERT
            })

        config.server = {
            host,
            port,
            https,
            strictPort: true,
            origin: `${protocol}://${host}:${port}`,
            fs: {
                strict: true,
                allow: ['node_modules', assets.base]
            }

            /***
             * For Windows user with files system watching not working
             * https://vitejs.dev/config/server-options.html#server-watch
             */

            /*
            watch: {
                usePolling: true,
                interval: 1000
            }
            */
        }
    }

    return config
})
