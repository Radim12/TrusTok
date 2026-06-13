print("MAIN START")

from fastapi import FastAPI
from fastapi.middleware.cors import CORSMiddleware
from pydantic import BaseModel

from data_handler import data_manager
print("DATA HANDLER OK")

# from model_handler import models_prediction
print("MODEL HANDLER SKIPPED")

@app.get("/")
def root():
    return {"status": "ok"}

app = FastAPI(title="TrustTok Sentiment Analysis API")

app.add_middleware(
    CORSMiddleware,
    allow_origins=["*"],
    allow_credentials=True,
    allow_methods=["*"],
    allow_headers=["*"],
)


class ProductRequest(BaseModel):
    product_name: str


@app.post("/get-metrics")
async def get_metrics(request: ProductRequest):
    target_product = request.product_name.lower()

    # 1. Filter awal dari CSV berdasarkan kata kunci input user
    filtered_df = data_manager.df[
        data_manager.df["clean_comment"].str.contains(target_product, na=False)
    ].copy()

    total_komen = len(filtered_df)

    if total_komen == 0:
        return {
            "status": "success",
            "product_selected": request.product_name,
            "total_comments": "0",
            "total_videos": "0",
            "comments": [],
            "brands": [],
            "sentiment_pie": {"neg": 0, "neu": 0, "pos": 0},
            "sentiment_metrics": {"neg": "0", "neu": "0", "pos": "0"},
        }

    # Ambil sampel (30 data teratas) untuk diproses live
    sample_df = filtered_df.head(30)

    list_komentar_ui = []
    brand_counts = {}
    sentiment_counts = {"negative": 0, "neutral": 0, "positive": 0}

    # Daftar hitam kata produk agar tidak bocor masuk ke grafik brand
    invalid_brand_words = {
        "toner",
        "moisturizer",
        "mois",
        "serum",
        "sunscreen",
        "facewash",
        "face wash",
        "cushion",
        "cleanser",
        "retinol",
        "bedak",
        "unknown",
        "produk",
        "product",
        "skincare",
        "you",
        "y",
    }

    # 2. Alirkan data melewati IndoBERT dan Bi-LSTM secara berurutan
    for idx, row in sample_df.iterrows():
        text_clean = row["clean_comment"]
        text_ori = row["comment"]

        # Jalankan Model IndoBERT NER
        brand_label, product_label = models_prediction.predict_ner_indobert(text_clean)
        
        print(f"DEBUG: Input: {text_clean[:20]}... | Raw Brand Label: {brand_label}")

        # Jalankan Model Bi-LSTM Sentimen
        sentiment_raw = models_prediction.predict_sentiment_bilstm(text_clean)

        # PENGAMAN SENTIMEN: Pastikan format huruf kecil dan tangani variasi string output
        sentiment_label = str(sentiment_raw).lower().strip()
        if "pos" in sentiment_label:
            sentiment_counts["positive"] += 1
        elif "neg" in sentiment_label:
            sentiment_counts["negative"] += 1
        else:
            sentiment_counts["neutral"] += 1

        # FILTER UTAMA BRAND: Bersihkan teks dan pastikan bukan termasuk produk/karakter sampah
        # ... di dalam loop for idx, row in sample_df.iterrows():
        if brand_label and brand_label != "unknown":
            # 1. Normalisasi: lowercase, hapus spasi berlebih
            brand_clean = " ".join(brand_label.lower().split())
            
            # --- DEBUGGING: CEK APAKAH INI TERDETEKSI ---
            print(f"DEBUG: Brand yang diproses: '{brand_clean}'")
            
            # --- SOLUSI NUKLIR ---
            if "wardah" in brand_clean:
                brand_clean = "wardah"
            
            print(f"DEBUG: Hasil akhir brand: '{brand_clean}'")
            # ---------------------------------------------

            # 2. Filter Agresif: Jika ada kata yang diawali "wardah" tapi diikuti huruf/kata sampah
            # Kita pecah menjadi bagian-bagian
            parts = brand_clean.split()
            
            # Jika bagian kedua hanya 1 karakter (seperti 'y'), buang bagian kedua
            if len(parts) > 1 and len(parts[1]) <= 1:
                brand_clean = parts[0]
            
            # Jika setelah dibersihkan brand_clean jadi cuma 1 huruf, buang total
            if len(brand_clean) <= 1:
                continue

            # 3. Filter terhadap daftar hitam (termasuk "wardah y" dsb jika perlu)
            if brand_clean in invalid_brand_words or "y" == brand_clean:
                continue

            # 4. Filter duplikasi (wardah wardah -> wardah)
            parts = brand_clean.split()
            if len(parts) > 1 and parts[0] == parts[1]:
                brand_clean = parts[0]

            # 5. Final check sebelum masuk dictionary
            if len(brand_clean) > 1:
                brand_formatted = brand_clean.title()
                brand_counts[brand_formatted] = brand_counts.get(brand_formatted, 0) + 1
                
        # Simpan 10 baris data untuk tabel komentar di frontend
        if len(list_komentar_ui) < 10:
            list_komentar_ui.append(
                {"id": f"r_{idx}", "original": text_ori, "cleansed": text_clean}
            )

    # 3. Hitung Kalkulasi Grafik Brand (Hanya brand riil terfilter)
    brand_charts = []
    total_brand_mentions = sum(brand_counts.values())
    total_unique_brands = len(
        brand_counts
    ) 

    if total_brand_mentions > 0:
        for brand, count in brand_counts.items():
            brand_charts.append(
                {
                    "name": f"{brand}",
                    "percentage": round((count / total_brand_mentions) * 100),
                }
            )
        brand_charts = sorted(brand_charts, key=lambda x: x["percentage"], reverse=True)
    else:
        brand_charts = [{"name": "Brand Lainnya", "percentage": 100}]

    # 4. Hitung Kalkulasi Grafik Sentimen (Bi-LSTM)
    total_processed = sum(sentiment_counts.values())
    pos_pct = (
        round((sentiment_counts["positive"] / total_processed) * 100)
        if total_processed > 0
        else 0
    )
    neu_pct = (
        round((sentiment_counts["neutral"] / total_processed) * 100)
        if total_processed > 0
        else 0
    )
    neg_pct = 100 - (pos_pct + neu_pct) if total_processed > 0 else 0

    return {
        "status": "success",
        "product_selected": request.product_name,
        "total_comments": f"{total_komen:,}",
        "total_videos": str(total_unique_brands),
        "comments": list_komentar_ui,
        "brands": brand_charts,
        "sentiment_pie": {"neg": neg_pct, "neu": neu_pct, "pos": pos_pct},
        "sentiment_metrics": {
            "neg": f"{sentiment_counts['negative']}",
            "neu": f"{sentiment_counts['neutral']}",
            "pos": f"{sentiment_counts['positive']}",
        },
    }


# @app.get("/")
# def read_root():
#     return {"message": "FastAPI Terintegrasi Model IndoBERT & Bi-LSTM Aktif."}
