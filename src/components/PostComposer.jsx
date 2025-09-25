import { createElement, useState, Fragment } from "@wordpress/element";
import { api } from "../api";

export default function PostComposer({ communities, onCreated }) {
  const [open, setOpen] = useState(false);
  const [title, setTitle] = useState("");
  const [communityId, setCommunityId] = useState("");
  const [content, setContent] = useState("");
  const [tags, setTags] = useState("");

  const submit = async () => {
    if (!title || !communityId) return;
    await api("/posts", {
      method: "POST",
      body: JSON.stringify({
        title,
        community_id: parseInt(communityId, 10),
        content,
        tags: tags
          .split(",")
          .map((t) => t.trim())
          .filter(Boolean),
      }),
    });
    setTitle("");
    setCommunityId("");
    setContent("");
    setTags("");
    setOpen(false);
    onCreated && onCreated();
  };

  if (!communeHub.currentUserId) {
    return createElement(
      "div",
      { className: "ch-card ch-post-composer" },
      "Log in to create posts."
    );
  }

  return createElement(
    "div",
    { className: "ch-card ch-post-composer" },
    !open &&
      createElement(
        "div",
        {
          style: { cursor: "text", color: "#64748b", fontSize: "14px" },
          onClick: () => setOpen(true),
        },
        "Create something awesome..."
      ),
    open &&
      createElement(
        Fragment,
        null,
        createElement("input", {
          placeholder: "Title",
          value: title,
          onChange: (e) => setTitle(e.target.value),
        }),
        createElement(
          "div",
          { className: "row" },
          createElement(
            "select",
            {
              value: communityId,
              onChange: (e) => setCommunityId(e.target.value),
            },
            createElement("option", { value: "" }, "Select community"),
            communities.map((c) =>
              createElement("option", { key: c.id, value: c.id }, c.name)
            )
          ),
          createElement("input", {
            placeholder: "tags (comma separated)",
            value: tags,
            onChange: (e) => setTags(e.target.value),
          })
        ),
        createElement("textarea", {
          rows: 5,
          placeholder: "Write...",
          value: content,
          onChange: (e) => setContent(e.target.value),
        }),
        createElement(
          "div",
          { className: "actions" },
          createElement(
            "button",
            {
              className: "ch-btn-outline ch-btn",
              onClick: () => setOpen(false),
            },
            "Cancel"
          ),
          createElement(
            "button",
            { className: "ch-btn", onClick: submit },
            "Publish"
          )
        )
      )
  );
}
