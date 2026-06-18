# Laporan Kumpulan Bug dan Kekurangan Program

Di bawah ini merupakan kumpulan bug dan kekurangan yang masih terdapat pada program.


## Kumpulan Bug Yang Terjadi Pada Program
1. **Dari Sisi User / Customer**
- Ketika user mengirim pesan untuk pertama kali nya setelah menekan tombol "help desk", pesan tersebut muncul double. misal nya user mengetikkan "Hallo", maka pada tampilan popup chat akan tampil seperti ini (index genap adalah pesan dari user dan ganjil dari bot / admin): [Hallo, Hallo ada yang bisa saya bantu?, Hallo, Hallo ada yang bisa saya bantu?].

- Ketika user dalam percakapan dengan admin, lalu percakapan selesai (ditutup oleh admin), maka user tidak dapat lagi mengetikkan pertanyaan untuk bertanya pertanyaan selanjutnya, dalam artian, area input user untuk menginputkan pesan terdisable (tidak dapat dibuat untuk menginputkan pesan). Hal ini terus berlanjut bahkan ketika saya sudah merefresh browser berkali kali.

2. **Dari Sisi Admin**
- Ada bug dimana ketika admin mencoba mengambil percakapan, namun area input bagi admin mengetikkan pesan tidak muncul, Sehingga admin tidak dapat membalas percakapan user walaupun sudah mengambil percakapan tersebut.


## Kumpulan Kekurangan Program
1. **Tidak Tersimpannya Histori Percakapan dengan Customer Setelah Percakapan Berakhir**, Di sisi admin, ketika admin mencoba mengakhiri percakapan dengan menekan tombol **tutup** yang berada di atas layar berdampingan dengan tombol **ambil percakapan**, maka percakapan tersebut akan langsung hilang. Memang, halaman **chat** pada sisi admin hanya memuat percakapan aktif, namun saya juga mau admin dapat melihat histori percakapan nya dengan semua **customer**. Maka dari itu, tolong buatkan satu lagi **halaman** di sisi **admin** bernama "Histori Percakapan" yang memuat percakapan percakapan admin dengan customer yang telah **selesai** atau **ditutup oleh admin**.

3. **Masalah AI Yang Terlalu Bodoh**, walaupun menggunakan dataset QnA dummy, namun saya telah mengatur dataset tersebut agar setiap satu pertanyaan dan jawaban diparafrase menjadi sepuluh pertanyaan dan sepuluh jawaban dengan bahasa yang berbeda beda dari formal hingga non formal. Namun tetap saja, ketika saya mencoba menanyakan sesuatu yang jelas jelas sesuai konteks yang ada di dataset atau mengetikkan pertanyaan yang sama persis ada di dataset yang sudah memiliki jawaban, AI tetap tidak dapat menjawab nya dan langsung meneruskannya kepada Admin. Saya mau kamu periksa dan terkait sistem RAG yang sudah saya buat ini. Untuk direkotorinya, bagian AI ini tersimpan pada direktori **AI/project**.