import { createElement } from "@wordpress/element";

export default function SearchBar({ search, onSearch }) {
  return createElement(
    "div",
    { className: "ch-search" },
    createElement("input", {
      placeholder: "Search posts...",
      value: search,
      onChange: (e) => onSearch(e.target.value),
    })
  );
}
