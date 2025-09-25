import { useState, useEffect, useCallback } from "@wordpress/element";
import { api } from "../api";

export function useForumData() {
  const [communities, setCommunities] = useState([]);
  const [posts, setPosts] = useState([]);
  const [sort, setSort] = useState("hot");
  const [community, setCommunity] = useState(0);
  const [search, setSearch] = useState("");
  const [loadingPosts, setLoadingPosts] = useState(false);

  const loadCommunities = useCallback(async () => {
    setCommunities(await api("/communities"));
  }, []);

  const loadPosts = useCallback(async () => {
    setLoadingPosts(true);
    const url = new URL(communeHub.root + "/posts");
    url.searchParams.set("sort", sort);
    if (community) url.searchParams.set("community", community);
    if (search) url.searchParams.set("search", search);
    const response = await fetch(url);
    const data = await response.json();
    setPosts(data.posts);
    setLoadingPosts(false);
  }, [sort, community, search]);

  useEffect(() => {
    loadCommunities();
  }, []);
  useEffect(() => {
    loadPosts();
  }, [loadPosts]);

  return {
    communities,
    posts,
    sort,
    setSort,
    community,
    setCommunity,
    search,
    setSearch,
    loadingPosts,
    reloadPosts: loadPosts,
  };
}
