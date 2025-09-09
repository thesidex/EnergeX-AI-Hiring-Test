import React from "react";
import { Link, Route, Routes, Navigate } from "react-router-dom";
import PostsPage from "./pages/Posts";
import AuthPage from "./pages/Auth";
import { useAuth } from "./pages/AuthContext";
import "./styles.css";

const App: React.FC = () => {
  const { user } = useAuth();
  return (
    <>
      <nav className="nav">
        <Link to="/">Posts</Link>
        <div className="spacer" />
        <Link to="/auth">{user ? "Account" : "Login/Register"}</Link>
      </nav>

      <Routes>
        <Route path="/" element={<PostsPage />} />
        <Route path="/auth" element={<AuthPage />} />
        <Route path="*" element={<Navigate to="/" replace />} />
      </Routes>
    </>
  );
};

export default App;
