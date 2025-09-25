import { defineConfig } from "vite";

export default defineConfig({
  build: {
    outDir: "assets/js",
    emptyOutDir: false,
    rollupOptions: {
      input: "src/index.js",
      output: {
        entryFileNames: "app.js",
        assetFileNames: "[name].[ext]",
        globals: {
          "@wordpress/element": "wp.element",
        },
      },
      external: ["@wordpress/element"],
    },
  },
});
