import { createElement } from "@wordpress/element";

export default function VoteBox({ post, onVote }) {
  const userVote = post.user_vote || 0;
  return createElement(
    "div",
    { className: "ch-vote-box" },
    createElement(
      "button",
      {
        className: userVote === 1 ? "active-up" : "",
        onClick: () => onVote(post.id, userVote === 1 ? "clear" : "up"),
      },
      "▲"
    ),
    createElement("div", { className: "ch-vote-score" }, post.votes.score),
    createElement(
      "button",
      {
        className: userVote === -1 ? "active-down" : "",
        onClick: () => onVote(post.id, userVote === -1 ? "clear" : "down"),
      },
      "▼"
    )
  );
}
