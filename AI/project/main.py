# pyrefly: ignore [missing-import]
from fastapi import FastAPI
from ai import chat_ai
# pyrefly: ignore [missing-import]
from fastapi.middleware.cors import CORSMiddleware
# pyrefly: ignore [missing-import]
from pydantic import BaseModel

app = FastAPI(title="Disty Chatbot API", version="2.0.0")

class ChatRequest(BaseModel):
    question: str

# ── CORS Origins ──────────────────────────────────────────────
# Izinkan akses dari: browser dev server, XAMPP (port 80/443),
# dan live server VS Code.
origins = [
    "http://localhost",
    "http://localhost:80",
    "http://127.0.0.1",
    "http://127.0.0.1:80",
    "http://localhost:5500",
    "http://127.0.0.1:5500",
]

app.add_middleware(
    CORSMiddleware,
    allow_origins=origins,
    allow_credentials=True,
    allow_methods=["*"],
    allow_headers=["*"],
)

# ── Frasa yang mengindikasikan AI tidak tahu jawaban ──────────
HANDOFF_PHRASES = [
    "tidak mengerti",
    "tidak tahu",
    "maaf saya tidak",
    "maaf, saya tidak",
    "tidak dapat menjawab",
    "di luar kemampuan saya",
]

def needs_human_agent(answer: str) -> bool:
    """Deteksi apakah respons AI memerlukan handoff ke manusia."""
    lower = answer.lower()
    return any(phrase in lower for phrase in HANDOFF_PHRASES)

# ── Endpoint: Chat biasa (backward-compatible) ────────────────
@app.post("/chat")
def ask_ai(chatRequest: ChatRequest):
    question = chatRequest.question
    answer   = chat_ai(question)
    return {
        "question": question,
        "answer":   answer,
    }

# ── Endpoint: Chat dengan deteksi handoff ────────────────────
@app.post("/detect-handoff")
def detect_handoff(chatRequest: ChatRequest):
    """
    Sama seperti /chat, tapi juga mengembalikan flag
    'needs_human' untuk digunakan oleh send-message.php.
    """
    question    = chatRequest.question
    answer      = chat_ai(question)
    human_needed = needs_human_agent(answer)

    return {
        "question":    question,
        "answer":      answer,
        "needs_human": human_needed,
    }

# ── Endpoint: Health check ────────────────────────────────────
@app.get("/health")
def health_check():
    return {"status": "ok", "service": "Disty Chatbot API"}