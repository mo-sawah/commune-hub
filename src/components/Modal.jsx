import { createElement } from "@wordpress/element";

export default function Modal({ title, onClose, children }) {
  return createElement(
    "div",
    { className: "ch-modal-overlay" },
    createElement(
      "div",
      { className: "ch-modal" },
      createElement("button", { className: "ch-close", onClick: onClose }, "✕"),
      createElement("h2", null, title),
      children
    )
  );
}
