import { createElement } from "@wordpress/element";

export default function CommunitySidebar({ communities }) {
  return createElement(
    "div",
    { className: "ch-card ch-sidebar-section" },
    createElement("h3", null, "Popular Communities"),
    communities.map((c) =>
      createElement(
        "div",
        { key: c.id, className: "ch-community-item" },
        createElement("div", null, "c/" + c.name),
        createElement(
          "span",
          { className: "ch-members-badge" },
          c.members + " members"
        )
      )
    )
  );
}
