# pyrefly: ignore [missing-import]
from fastapi import FastAPI
from ai import chat_ai
# pyrefly: ignore [missing-import]
from fastapi.middleware.cors import CORSMiddleware
# pyrefly: ignore [missing-import]
from pydantic import BaseModel
app = FastAPI()

class ChatRequest(BaseModel):
    question: str

origins = [
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

@app.post("/chat")
def ask_ai(chatRequest: ChatRequest):
    question = chatRequest.question
    answer = chat_ai(question)
    return {
        "question": question,
        "answer": answer
    }

@app.get("/hallo")
def sayHallo():
    return "Hallo World"