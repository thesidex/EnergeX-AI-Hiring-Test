import { useState } from "react";
import type { FormEvent } from "react";
import { login } from "./api";
import { useAuth } from "./AuthContext";

export default function Login() {
  const { setAuth } = useAuth();
  const [email,setEmail]=useState("ayesha@example.com");
  const [password,setPassword]=useState("secret123");
  const [err,setErr]=useState<string|null>(null);

  const onSubmit = async (e:FormEvent) => {
    e.preventDefault(); setErr(null);
    try { const r = await login(email,password); setAuth({token:r.token, user:r.user}); }
    catch(e:any){ setErr(e?.response?.data?.error || "Login failed"); }
  };

  return (<div className="card">
    <h2>Login</h2>
    <form onSubmit={onSubmit}>
      <input placeholder="Email" value={email} onChange={e=>setEmail(e.target.value)} />
      <input placeholder="Password" type="password" value={password} onChange={e=>setPassword(e.target.value)} />
      <button>Login</button>
    </form>
    {err && <p className="error">{err}</p>}
  </div>);
}
