import React, { useEffect, useState } from "react";
import { createPost, deletePost, listPosts, updatePost } from "./api";
import type { Post } from "./api";
import { useAuth } from "./AuthContext";

const PostsPage: React.FC = () => {
  const { user, token, logout } = useAuth();
  const [posts, setPosts] = useState<Post[]>([]);
  const [title, setTitle] = useState("");
  const [content, setContent] = useState("");
  const [error, setError] = useState<string | null>(null);

  const load = async () => {
    const { data } = await listPosts();
    setPosts(data);
  };

  useEffect(() => {
    load();
  }, []);

  const onCreate = async (e: React.FormEvent<HTMLFormElement>) => {
    e.preventDefault();
    setError(null);
    try {
      await createPost(title, content);
      setTitle("");
      setContent("");
      await load();
    } catch (err: unknown) {
      if (err && typeof err === "object" && "response" in err) {
        const axiosErr = err as { response?: { data?: { error?: string } } };
        setError(axiosErr.response?.data?.error || "Create failed");
      } else {
        setError("Unexpected error");
      }
    }
  };

  const onUpdate = async (p: Post) => {
    const t = prompt("New title", p.title);
    const c = prompt("New content", p.content);
    if (t == null || c == null) return;

    try {
      await updatePost(p.id, t, c);
      await load();
    } catch (err: unknown) {
      let msg = "Update failed";
      if (err && typeof err === "object" && "response" in err) {
        const e = err as { response?: { data?: { error?: string } } };
        msg = e.response?.data?.error ?? msg;
      }
      alert(msg);
    }
  };

  const onDelete = async (p: Post) => {
    if (!confirm("Delete this post?")) return;

    try {
      await deletePost(p.id);
      await load();
    } catch (err: unknown) {
      let msg = "Delete failed";
      if (err && typeof err === "object" && "response" in err) {
        const e = err as { response?: { data?: { error?: string } } };
        msg = e.response?.data?.error ?? msg;
      }
      alert(msg);
    }
  };

  return (
    <div className="container">
      <header className="header">
        <h2>Posts</h2>
        <div>
          {user ? (
            <>
              <span className="me-2">Hello, {user.name}</span>
              <button onClick={logout}>Logout</button>
            </>
          ) : (
            <span>Browsing as guest</span>
          )}
        </div>
      </header>

      {token && (
        <form onSubmit={onCreate} className="card mb">
          <h3>Create Post</h3>
          <input placeholder="Title" value={title} onChange={(e) => setTitle(e.target.value)} />
          <textarea placeholder="Content" value={content} onChange={(e) => setContent(e.target.value)} />
          {error && <p className="error">{error}</p>}
          <button>Create</button>
        </form>
      )}

      <ul className="list">
        {posts.map((p) => (
          <li key={p.id} className="card">
            <div className="post-head">
              <strong>{p.title}</strong>
              <small>by user #{p.user_id}</small>
            </div>
            <p>{p.content}</p>
            {token && user?.id === p.user_id && (
              <div className="actions">
                <button onClick={() => onUpdate(p)}>Edit</button>
                <button onClick={() => onDelete(p)} className="danger">Delete</button>
              </div>
            )}
          </li>
        ))}
        {posts.length === 0 && <p>No posts yet.</p>}
      </ul>
    </div>
  );
};

export default PostsPage;
