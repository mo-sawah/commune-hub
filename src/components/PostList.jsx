import { createElement } from "@wordpress/element";
import PostCard from "./PostCard.jsx";

export default function PostList({ posts, loading, onVote }) {
  if (loading && !posts.length) {
    return createElement(
      "div",
      { className: "ch-card ch-empty" },
      "Loading..."
    );
  }
  if (!loading && !posts.length) {
    return createElement(
      "div",
      { className: "ch-card ch-empty" },
      "No posts found."
    );
  }
  return posts.map((p) =>
    createElement(PostCard, { key: p.id, post: p, onVote })
  );
}
