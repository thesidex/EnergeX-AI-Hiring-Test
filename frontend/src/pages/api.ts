import axios from "axios";

const api = axios.create({
  baseURL: import.meta.env.VITE_API_BASE_URL || "http://localhost:8000",
  headers: { "Content-Type": "application/json" },
});

api.interceptors.request.use((config) => {
  const token = localStorage.getItem("token");
  if (token) config.headers.Authorization = `Bearer ${token}`;
  return config;
});

// ---- Types ----
export type User = { id: number; name: string; email: string };
export type Post = { id: number; title: string; content: string; user_id: number };

// ---- Endpoints ----
export const register = (name: string, email: string, password: string) =>
  api.post("/api/register", { name, email, password });

export const login = (email: string, password: string) =>
  api.post("/api/login", { email, password });

export const listPosts = () => api.get<Post[]>("/api/posts");
export const getPost = (id: number) => api.get<Post>(`/api/posts/${id}`);
export const createPost = (title: string, content: string) =>
  api.post<Post>("/api/posts", { title, content });
export const updatePost = (id: number, title: string, content: string) =>
  api.put<Post>(`/api/posts/${id}`, { title, content });
export const deletePost = (id: number) => api.delete(`/api/posts/${id}`);

export default api;
