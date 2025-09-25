import { createElement, Fragment } from "@wordpress/element";
import { useForumData } from "../hooks/useForumData";
import PostList from "./PostList.jsx";
import CommunitySidebar from "./CommunitySidebar.jsx";
import SortTabs from "./SortTabs.jsx";
import PostComposer from "./PostComposer.jsx";
import SearchBar from "./SearchBar.jsx";

export default function AppShell() {
  const d = useForumData();
  return createElement(
    "div",
    { className: "ch-app-shell" },
    createElement(
      "div",
      { className: "ch-header" },
      createElement("div", { className: "ch-logo" }, "CommuneHub"),
      createElement(SearchBar, {
        search: d.search,
        onSearch: (v) => d.setSearch(v),
      }),
      createElement(
        "button",
        { className: "ch-btn", onClick: d.reloadPosts },
        "Refresh"
      )
    ),
    createElement(
      "div",
      { className: "ch-layout" },
      createElement(
        "div",
        null,
        createElement(PostComposer, {
          communities: d.communities,
          onCreated: d.reloadPosts,
        }),
        createElement(SortTabs, { active: d.sort, onChange: d.setSort }),
        createElement(PostList, {
          posts: d.posts,
          loading: d.loadingPosts,
          onVote: () => {},
        })
      ),
      createElement(
        "div",
        null,
        createElement(CommunitySidebar, { communities: d.communities })
      )
    )
  );
}
