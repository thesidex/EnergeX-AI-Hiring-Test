import 'dotenv/config';
import express from 'express';
import helmet from 'helmet';
import cors from 'cors';
import morgan from 'morgan';
import Redis from 'ioredis';
import mysql from 'mysql2/promise';

const app = express();
app.use(helmet());
app.use(cors({ origin: process.env.CORS_ORIGIN || 'http://localhost:5173' }));
app.use(express.json());
app.use(morgan('dev'));

const PORT = Number(process.env.PORT || 4000);
const CACHE_TTL = Number(process.env.CACHE_TTL || 120);

const redis = new Redis({
  host: process.env.REDIS_HOST || 'redis',
  port: Number(process.env.REDIS_PORT || 6379)
});

const pool = mysql.createPool({
  host: process.env.MYSQL_HOST || 'mysql',
  port: Number(process.env.MYSQL_PORT || 3306),
  user: process.env.MYSQL_USER || 'app',
  password: process.env.MYSQL_PASSWORD || 'app',
  database: process.env.MYSQL_DATABASE || 'energex',
  waitForConnections: true,
  connectionLimit: 10
});

app.get('/cache/posts', async (_req, res) => {
  const key = 'posts:all';
  try {
    const hit = await redis.get(key);
    if (hit) {
      return res.json(JSON.parse(hit));
    }
    const [rows] = await pool.query(
      'SELECT id, title, content, user_id, created_at FROM posts ORDER BY created_at DESC'
    );
    await redis.setex(key, CACHE_TTL, JSON.stringify(rows));
    res.json(rows);
  } catch (e: any) {
    res.status(500).json({ error: 'Cache server error', detail: e?.message });
  }
});

app.get('/cache/posts/:id', async (req, res) => {
  const id = Number(req.params.id || 0);
  const key = `posts:${id}`;
  try {
    const hit = await redis.get(key);
    if (hit) {
      return res.json(JSON.parse(hit));
    }
    const [rows] = await pool.query(
      'SELECT id, title, content, user_id, created_at FROM posts WHERE id = ? LIMIT 1',
      [id]
    );
    const post = Array.isArray(rows) && rows[0] ? rows[0] : null;
    if (!post) return res.status(404).json({ error: 'Not found' });
    await redis.setex(key, CACHE_TTL, JSON.stringify(post));
    res.json(post);
  } catch (e: any) {
    res.status(500).json({ error: 'Cache server error', detail: e?.message });
  }
});

app.listen(PORT, () => {
  console.log(`node-cache listening on :${PORT}`);
});
