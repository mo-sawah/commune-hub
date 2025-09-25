/**
 * Postbuild script for Commune Hub
 * - Adds a banner to the final JS bundle
 * - (Optional) can copy or transform assets
 * Run after: vite build
 */
import fs from "fs";
import path from "path";

const distFile = path.resolve("assets/js/app.js");

function addBanner(file) {
  if (!fs.existsSync(file)) {
    console.warn("[postbuild] app.js not found at", file);
    return;
  }
  let content = fs.readFileSync(file, "utf8");
  const banner = `/** Commune Hub build: ${new Date().toISOString()} */`;
  if (!content.startsWith("/** Commune Hub build")) {
    content = banner + "\n" + content;
    fs.writeFileSync(file, content, "utf8");
    console.log("[postbuild] Banner injected.");
  } else {
    console.log("[postbuild] Banner already present.");
  }
}

function main() {
  console.log("[postbuild] Starting post-build tasks...");
  addBanner(distFile);

  // Example: If you ever want to merge additional CSS:
  // const extraCss = path.resolve('src/extra.css');
  // if (fs.existsSync(extraCss)) {
  //   const targetCss = path.resolve('assets/css/frontend.css');
  //   const merged = fs.readFileSync(targetCss,'utf8') + '\n/* Extra */\n' + fs.readFileSync(extraCss,'utf8');
  //   fs.writeFileSync(targetCss, merged, 'utf8');
  //   console.log('[postbuild] Merged extra.css into frontend.css');
  // }

  console.log("[postbuild] Done.");
}

main();
