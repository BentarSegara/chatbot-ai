import os
from dotenv import load_dotenv
from langchain_chroma import Chroma
from langchain_huggingface import HuggingFaceEmbeddings
from langchain_groq import ChatGroq
# from langchain_google_genai import GoogleGenerativeAIEmbeddings, ChatGoogleGenerativeAI
from langchain_core.prompts import ChatPromptTemplate
from langchain_core.runnables import RunnablePassthrough
from langchain_core.output_parsers import StrOutputParser

load_dotenv()


GROQ_API_KEY = os.getenv("API_KEY")
embeddings = HuggingFaceEmbeddings(model_name="sentence-transformers/all-MiniLM-L6-v2")

vector_store = Chroma(
    persist_directory="./chroma_db", 
    embedding_function= embeddings
)
retriever = vector_store.as_retriever(search_kwargs={"k": 2})

llm = ChatGroq(model="llama-3.3-70b-versatile", temperature=0)
template = """
Anda adalah asisten Customer Support yang sangat membantu.
Jawablah pertanyaan pelanggan hanya berdasarkan konteks yang diberikan di bawah ini.
Jika pelanggan mengetikkan kalimat sapaan, maka jawab lah sapaan tersebut.
Jika pelanggan mengetikkan kalimat yang bukan sapaan dan anda tidak tahu jawabannya berdasarkan konteks, katakan saja Maaf saya tidak mengerti dengan pertanyaan anda.

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