import express from "express";
import axios from "axios";

const app = express();
const PORT = 3000;

// Dify config
const DIFY_URL = "https://api.dify.ai/v1/chat-messages";
const DIFY_API_KEY = "app-d1ljBYvzFLCMqpHzaKZdSvOM";

// ✅ ROOT ENDPOINT
app.get("/", (req, res) => {
  res.json({
    status: "ok",
    message: "🤖 AI is running"
  });
});

// CHAT ENDPOINT
app.get("/api/chat", async (req, res) => {
  const prompt = req.query.prompt;

  if (!prompt) {
    return res.status(400).json({
      error: "Missing prompt query parameter"
    });
  }

  try {
    const response = await axios.post(
      DIFY_URL,
      {
        conversation_id: "",
        inputs: {},
        query: prompt,
        user: "05511691-0e43-4ae4-94a6-8b913ab73bde_sc"
      },
      {
        headers: {
          Authorization: `Bearer ${DIFY_API_KEY}`,
          "Content-Type": "application/json"
        }
      }
    );

    res.json(response.data);
  } catch (error) {
    res.status(500).json({
      error: "Failed to call Dify API",
      details: error.response?.data || error.message
    });
  }
});

// OPTIONAL: catch-all for unknown endpoints
app.use((req, res) => {
  res.status(404).json({
    error: "Endpoint not found",
    message: "🤖 AI is running but endpoint does not exist"
  });
});

app.listen(PORT, () => {
  console.log(`🚀 Server running at http://localhost:${PORT}`);
});
