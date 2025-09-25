import { createElement } from "@wordpress/element";
import VoteBox from "./VoteBox.jsx";

export default function PostCard({ post, onVote }) {
  return createElement(
    "div",
    { className: "ch-card ch-post" },
    createElement(VoteBox, { post, onVote }),
    createElement(
      "div",
      { className: "ch-post-content" },
      createElement(
        "div",
        { className: "ch-post-meta" },
        createElement("span", null, "c/" + (post.community_name || "general")),
        createElement("span", null, "•"),
        createElement("span", null, post.author),
        createElement("span", null, "•"),
        createElement("span", null, new Date(post.time).toLocaleString())
      ),
      createElement("div", { className: "ch-post-title" }, post.title),
      createElement(
        "div",
        { className: "ch-tags" },
        post.tags &&
          post.tags.map((tag) =>
            createElement("span", { className: "ch-tag", key: tag }, tag)
          )
      ),
      createElement(
        "div",
        { className: "ch-actions" },
        createElement("button", null, "Comments (" + post.comment_count + ")"),
        createElement("button", null, "Share"),
        createElement("button", null, "Report")
      )
    )
  );
}
