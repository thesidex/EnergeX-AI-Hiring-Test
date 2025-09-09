import express from "express";
import Redis from "ioredis";
import mysql from "mysql2/promise";

async function main() {
  const app = express();
  const redis = new Redis({ host: "127.0.0.1", port: 6379 });

  const db = await mysql.createPool({
    host: "127.0.0.1",
    user: "root",
    password: "secret",
    database: "energeX",
  });

  app.get("/cache/posts", async (_req, res) => {
    const cached = await redis.get("posts_all");
    if (cached) return res.json(JSON.parse(cached));

    const [rows] = await db.query("SELECT * FROM posts ORDER BY id DESC");
    await redis.set("posts_all", JSON.stringify(rows), "EX", 60);
    res.json(rows);
  });

  app.get("/cache/posts/:id", async (req, res) => {
    const key = `post_${req.params.id}`;
    const cached = await redis.get(key);
    if (cached) return res.json(JSON.parse(cached));

    const [rows]: any = await db.query("SELECT * FROM posts WHERE id = ?", [
      req.params.id,
    ]);
    if (!rows[0]) return res.status(404).json({ error: "Not found" });

    await redis.set(key, JSON.stringify(rows[0]), "EX", 60);
    res.json(rows[0]);
  });

  app.listen(4000, () =>
    console.log("Cache service running at http://localhost:4000")
  );
}

main().catch((err) => {
  console.error("Fatal error:", err);
  process.exit(1);
});
