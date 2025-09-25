import fs from "fs";
import path from "path";

const file = path.resolve("assets/js/app.js");

function addBanner() {
  if (!fs.existsSync(file)) return;
  let content = fs.readFileSync(file, "utf8");
  const banner = `/** Commune Hub build: ${new Date().toISOString()} */`;
  if (!content.startsWith("/** Commune Hub build")) {
    fs.writeFileSync(file, banner + "\n" + content, "utf8");
    console.log("[postbuild] Banner added.");
  } else {
    console.log("[postbuild] Banner already present.");
  }
}

addBanner();
