import React, { useState } from "react";
import { useAuth } from "./AuthContext";

const AuthPage: React.FC = () => {
  const { login, register } = useAuth();
  const [mode, setMode] = useState<"login" | "register">("login");
  const [name, setName] = useState("");
  const [email, setEmail] = useState("t@example.com");
  const [password, setPassword] = useState("secret123");
  const [error, setError] = useState<string | null>(null);
  const [busy, setBusy] = useState(false);

  const onSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    setBusy(true);
    setError(null);
    try {
      if (mode === "login") await login(email, password);
      else await register(name, email, password);
    } catch (err: any) {
      setError(err?.response?.data?.error || "Request failed");
    } finally {
      setBusy(false);
    }
  };

  return (
    <div className="container">
      <h2>{mode === "login" ? "Login" : "Register"}</h2>
      <form onSubmit={onSubmit} className="card">
        {mode === "register" && (
          <input placeholder="Name" value={name} onChange={(e) => setName(e.target.value)} />
        )}
        <input placeholder="Email" value={email} onChange={(e) => setEmail(e.target.value)} />
        <input placeholder="Password" type="password" value={password} onChange={(e) => setPassword(e.target.value)} />
        {error && <p className="error">{error}</p>}
        <button disabled={busy}>{busy ? "Please wait..." : mode === "login" ? "Login" : "Register"}</button>
      </form>
      <button className="link" onClick={() => setMode(mode === "login" ? "register" : "login")}>
        {mode === "login" ? "Need an account? Register" : "Have an account? Login"}
      </button>
    </div>
  );
};

export default AuthPage;
