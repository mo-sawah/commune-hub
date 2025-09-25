export const api = async (path, options = {}) => {
  const res = await fetch(communeHub.root + path, {
    credentials: "same-origin",
    headers: {
      "Content-Type": "application/json",
      "X-WP-Nonce": communeHub.nonce,
    },
    ...options,
  });
  const data = await res.json();
  if (!res.ok) throw data;
  return data;
};
