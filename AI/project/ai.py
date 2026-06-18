import os
# pyrefly: ignore [missing-import]
from dotenv import load_dotenv
# pyrefly: ignore [missing-import]
from langchain_chroma import Chroma
# pyrefly: ignore [missing-import]
from langchain_huggingface import HuggingFaceEmbeddings
# pyrefly: ignore [missing-import]
from langchain_groq import ChatGroq
# pyrefly: ignore [missing-import]
from langchain_core.prompts import ChatPromptTemplate
# pyrefly: ignore [missing-import]
from langchain_core.runnables import RunnablePassthrough
# pyrefly: ignore [missing-import]
from langchain_core.output_parsers import StrOutputParser

load_dotenv()


GROQ_API_KEY = os.getenv("GROQ_API_KEY")

# Gunakan model multilingual agar lebih akurat untuk Bahasa Indonesia
embeddings = HuggingFaceEmbeddings(
    model_name="sentence-transformers/paraphrase-multilingual-MiniLM-L12-v2"
)

vector_store = Chroma(
    persist_directory="./chroma_db", 
    embedding_function=embeddings
)
# Naikkan k dari 2 ke 5 agar LLM mendapat lebih banyak konteks yang relevan
retriever = vector_store.as_retriever(search_kwargs={"k": 5})

llm = ChatGroq(model="llama-3.3-70b-versatile", temperature=0)
template = """
Anda adalah asisten Customer Support PT Disty Teknologi yang ramah, cerdas, dan sangat membantu.
Tugas Anda adalah menjawab pertanyaan pelanggan berdasarkan konteks yang diberikan.

Aturan penting:
1. Jika pelanggan mengirimkan sapaan (misal: halo, hi, selamat pagi, dll), balas dengan ramah.
2. Gunakan konteks di bawah untuk menjawab pertanyaan pelanggan dengan sebaik mungkin.
3. Jika konteks mengandung informasi yang relevan meskipun tidak persis sama dengan pertanyaan,
   tetap gunakan informasi tersebut untuk membentuk jawaban yang membantu.
4. Hanya jika konteks benar-benar tidak mengandung informasi yang berhubungan dengan pertanyaan,
   katakan: "Maaf saya tidak mengerti dengan pertanyaan anda."
5. Jawab dalam Bahasa Indonesia yang natural dan sesuai gaya bicara pelanggan.

Konteks:
{context}

Pertanyaan Pelanggan: {question}
"""
prompt = ChatPromptTemplate.from_template(template)

def format_docs(docs):
    return "\n\n".join(doc.page_content for doc in docs)

rag_chain = (
    {"context": retriever | format_docs, "question": RunnablePassthrough()}
    | prompt
    | llm
    | StrOutputParser()
)

def chat_ai(question: str) -> str:
    print(f"pertanyaan user: {question}")
    print("Sedang berpikir...")

    jawaban = rag_chain.invoke(question)

    print("Berpikir selesai")
    return jawaban