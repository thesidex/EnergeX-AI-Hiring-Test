import { useState } from "react";
import type { FormEvent } from "react";
import { register } from "./api";

export default function Register() {
  const [name,setName]=useState("Ayesha");
  const [email,setEmail]=useState("ayesha@example.com");
  const [password,setPassword]=useState("secret123");
  const [msg,setMsg]=useState<string|null>(null);
  const [err,setErr]=useState<string|null>(null);

  const onSubmit = async (e:FormEvent) => {
    e.preventDefault(); setMsg(null); setErr(null);
    try { const r = await register(name,email,password); setMsg(`Registered id=${r.id}`); }
    catch(e:any){ setErr(e?.response?.data?.error || "Register failed"); }
  };

  return (<div className="card">
    <h2>Register</h2>
    <form onSubmit={onSubmit}>
      <input placeholder="Name" value={name} onChange={e=>setName(e.target.value)} />
      <input placeholder="Email" value={email} onChange={e=>setEmail(e.target.value)} />
      <input placeholder="Password" type="password" value={password} onChange={e=>setPassword(e.target.value)} />
      <button>Register</button>
    </form>
    {msg && <p className="ok">{msg}</p>}
    {err && <p className="error">{err}</p>}
  </div>);
}
