import csv
import os
from dotenv import load_dotenv
from langchain_community.document_loaders.csv_loader import CSVLoader
from langchain_huggingface import HuggingFaceEmbeddings
from langchain_google_genai import GoogleGenerativeAIEmbeddings
from langchain_chroma import Chroma

load_dotenv()

API_KEY = os.getenv("API_KEY")
loader = CSVLoader(
    file_path="C:\Projects\Python\AI Chatbot\project\dataset\Dataset_QnA_Disty.csv", 
    source_column="Jenis",
    encoding='utf-8'
    )

print("Memecah dokumen menjadi potongan - potongan.....")
chunks = loader.load()

# Inisialisasi model embedding dari Groq
embeddings = HuggingFaceEmbeddings(model_name="sentence-transformers/all-MiniLM-L6-v2")

# Masukkan chunks ke dalam ChromaDB
print("Memasukkan potongan - potongan dokumen ke dalam ChromaDB.....")
vector_store = Chroma.from_documents(
    documents=chunks,
    embedding=embeddings,
    persist_directory="./chroma_db" # Opsional: simpan database secara lokal
)
print("Proses embbeding selesai")
