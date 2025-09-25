import { defineConfig } from "vite";

export default defineConfig({
  build: {
    lib: {
      entry: "src/index.js",
      name: "CommuneHubApp",
      fileName: () => "app.js",
      formats: ["iife"],
    },
    outDir: "assets/js",
    emptyOutDir: false,
    cssCodeSplit: false,
    rollupOptions: {
      external: ["@wordpress/element"],
      output: {
        globals: {
          "@wordpress/element": "wp.element",
        },
      },
    },
  },
});
