// Example dev entry if you build with Vite (not required for initial usage).
import App from "./components/AppShell.jsx";
import "./style.css";
const { createElement, render } = wp.element;

document.addEventListener("DOMContentLoaded", () => {
  const root = document.getElementById("commune-hub-app");
  if (root) {
    render(createElement(App), root);
  }
});
