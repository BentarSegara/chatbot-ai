import csv
import os
# pyrefly: ignore [missing-import]
from dotenv import load_dotenv
# pyrefly: ignore [missing-import]
from langchain_community.document_loaders.csv_loader import CSVLoader
# pyrefly: ignore [missing-import]
from langchain_huggingface import HuggingFaceEmbeddings
# pyrefly: ignore [missing-import]
from langchain_google_genai import GoogleGenerativeAIEmbeddings
# pyrefly: ignore [missing-import]
from langchain_chroma import Chroma

load_dotenv()

API_KEY = os.getenv("GROQ_API_KEY")

# Path relative agar bisa berjalan dari direktori mana pun
BASE_DIR   = os.path.dirname(os.path.abspath(__file__))
DATASET_PATH = os.path.join(BASE_DIR, "dataset", "Dataset_QnA_Disty.csv")

loader = CSVLoader(
    file_path=DATASET_PATH,
    source_column="Jenis",
    encoding='utf-8'
    )

print("Memecah dokumen menjadi potongan - potongan.....")
chunks = loader.load()

# Gunakan model multilingual yang sama dengan ai.py agar embedding konsisten
embeddings = HuggingFaceEmbeddings(
    model_name="sentence-transformers/paraphrase-multilingual-MiniLM-L12-v2"
)

# Masukkan chunks ke dalam ChromaDB
print("Memasukkan potongan - potongan dokumen ke dalam ChromaDB.....")
vector_store = Chroma.from_documents(
    documents=chunks,
    embedding=embeddings,
    persist_directory="./chroma_db" # Opsional: simpan database secara lokal
)
print("Proses embbeding selesai")
