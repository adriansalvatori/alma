import fs from 'fs';
import path from 'path';
import glob from 'fast-glob';

export default function bladeBlocksPlugin() {
    return {
        name: 'vite-plugin-blade-blocks',
        buildStart() {
            const blocksDir = path.resolve(__dirname, 'resources/views/blocks');
            const outputDir = path.resolve(__dirname, 'resources/.blocks');

            if (!fs.existsSync(outputDir)) {
                fs.mkdirSync(outputDir, { recursive: true });
            }

            const files = glob.sync('**/*.blade.php', { cwd: blocksDir });

            files.forEach(file => {
                const filePath = path.resolve(blocksDir, file);
                const content = fs.readFileSync(filePath, 'utf-8');
                
                // Match the JSON block inside standard Blade comments at the very top
                const match = content.match(/^{{--\s*([\s\S]*?)\s*--}}/);
                
                if (match) {
                    try {
                        const jsonString = match[1];
                        const blockData = JSON.parse(jsonString);
                        
                        // Default filename without extension
                        const blockBaseName = path.basename(file, '.blade.php');
                        
                        // Calculate relative path from output directory to the blade file
                        // Output is: resources/.blocks/[blockBaseName]/block.json
                        // Blade is: resources/views/blocks/[file]
                        const relativePath = `../../views/blocks/${file}`;
                        blockData.render = `file:${relativePath}`;
                        
                        const blockOutputDir = path.resolve(outputDir, blockBaseName);
                        if (!fs.existsSync(blockOutputDir)) {
                            fs.mkdirSync(blockOutputDir, { recursive: true });
                        }
                        
                        const blockJsonPath = path.resolve(blockOutputDir, 'block.json');
                        fs.writeFileSync(blockJsonPath, JSON.stringify(blockData, null, 2));
                        
                        console.log(`[Blade Blocks] Generated block.json for ${blockData.name}`);
                    } catch (e) {
                        console.error(`[Blade Blocks] Error parsing JSON frontmatter in ${file}`, e.message);
                    }
                }
            });
        }
    };
}
