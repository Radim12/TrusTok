import io
import json
import os
import pickle
import zipfile
from huggingface_hub import hf_hub_download
from keras.preprocessing.sequence import pad_sequences
import numpy as np
import tensorflow as tf
import torch
from transformers import AutoModelForTokenClassification, AutoTokenizer


def load_and_fix_keras_model(model_path):
    """Memperbaiki bug internal 'quantization_config' pada file .keras"""
    if not os.path.exists(model_path):
        raise FileNotFoundError(f"File model tidak ditemukan di: {model_path}")

    print("🛠️ Sedang melakukan patch otomatis pada config file .keras...")
    with open(model_path, "rb") as f:
        zip_data = io.BytesIO(f.read())

    temp_model_path = model_path.replace(".keras", "_fixed_temp.keras")

    with zipfile.ZipFile(zip_data, "r") as z_in:
        with zipfile.ZipFile(
            temp_model_path, "w", compression=z_in.compression
        ) as z_out:
            for item in z_in.infolist():
                data = z_in.read(item.filename)
                if item.filename == "config.json":
                    config_dict = json.loads(data.decode("utf-8"))
                    config_str = json.dumps(config_dict)
                    config_str = config_str.replace('"quantization_config": null,', "")
                    config_str = config_str.replace(', "quantization_config": null', "")
                    config_str = config_str.replace('"quantization_config": null', "")
                    data = config_str.encode("utf-8")
                    print("✅ Parameter 'quantization_config' berhasil dibersihkan!")
                z_out.writestr(item, data)

    try:
        model = tf.keras.models.load_model(temp_model_path, compile=False)
        return model
    finally:
        if os.path.exists(temp_model_path):
            os.remove(temp_model_path)


class ModelPredictor:
    def __init__(self):
        self.repo_id = "pyogaaa/TrustTok"
        print("⏳ Mendownload aset model...")
        
        # Gunakan list file yang lengkap
        files = ["indobert/config.json", "indobert/tokenizer_config.json", "indobert/model.safetensors"]
        
        # Download semua dan ambil folder path dari salah satu file
        paths = [hf_hub_download(repo_id=self.repo_id, filename=f) for f in files]
        folder_path = os.path.dirname(paths[0])
        
        # Load model sekali saja
        # Ganti seluruh bagian awal __init__ menjadi ini:
        self.bert_model = AutoModelForTokenClassification.from_pretrained("pyogaaa/TrustTok", subfolder="indobert")
        self.bert_tokenizer = AutoTokenizer.from_pretrained("pyogaaa/TrustTok", subfolder="indobert", use_fast=False)
        
        self.id2tag = {0: "O", 1: "B-BRAND", 2: "I-BRAND", 3: "B-PRODUCT", 4: "I-PRODUCT"}
        
        # 2. LOAD MODEL BI-LSTM SENTIMEN
        print("⏳ Memuat Model Bi-LSTM Sentimen Keras...")
        lstm_model_path = hf_hub_download(repo_id=self.repo_id, filename="bilstm/model_sentimen.keras")
        tokenizer_path = hf_hub_download(repo_id=self.repo_id, filename="bilstm/tokenizer.pkl")
        config_path = hf_hub_download(repo_id=self.repo_id, filename="bilstm/config.pkl")
        encoder_path = hf_hub_download(repo_id=self.repo_id, filename="bilstm/encoder.pkl")

        self.lstm_model = load_and_fix_keras_model(lstm_model_path)

        print("⏳ Memuat Tokenizer, Config, dan Encoder Bi-LSTM...")
        with open(tokenizer_path, "rb") as f: self.lstm_tokenizer = pickle.load(f)
        with open(config_path, "rb") as f: 
            self.lstm_config = pickle.load(f)
            self.maxlen = self.lstm_config.get("maxlen", 40)
        with open(encoder_path, "rb") as f: self.label_encoder = pickle.load(f)

        # Daftar hitam kata umum / stopword yang sering salah dikenali sebagai brand
        self.ignored_words = {
            "you",
            "y",
            "faceto",
            "ke",
            "yg",
            "dan",
            "di",
            "ini",
            "itu",
            "ya",
            "ga",
            "ada",
            "yang",
            "untuk",
            "dari",
            "dengan",
            "saya",
            "aku",
            "kamu",
            "banget",
        }

        print("✅ Semua Model Berhasil Dimuat dari File Lokal!")

    def predict_ner_indobert(self, text: str):
        if not text.strip():
            return "unknown", "unknown"

        inputs = self.bert_tokenizer(
            text, return_tensors="pt", truncation=True, padding=True
        )
        with torch.no_grad():
            outputs = self.bert_model(**inputs)

        predictions = torch.argmax(outputs.logits, dim=2)
        tokens = self.bert_tokenizer.convert_ids_to_tokens(inputs["input_ids"][0])
        tags = [self.id2tag.get(p.item(), "O") for p in predictions[0]]

        brand_tokens = []
        product_tokens = []

        last_brand_token = None
        last_prod_token = None

        for token, tag in zip(tokens, tags):
            if token in ["[CLS]", "[SEP]", "[PAD]"]:
                continue

            clean_token = token.replace("##", "")

            # PROSES BRAND
            if "BRAND" in tag:
                if clean_token != last_brand_token:
                    if tag.startswith("B-") and brand_tokens:
                        brand_tokens.append(" ")
                    brand_tokens.append(clean_token)
                    last_brand_token = clean_token

            # PROSES PRODUCT
            elif "PRODUCT" in tag:
                if clean_token != last_prod_token:
                    if tag.startswith("B-") and product_tokens:
                        product_tokens.append(" ")
                    product_tokens.append(clean_token)
                    last_prod_token = clean_token

        # Join dan Normalisasi
        detected_brand = " ".join("".join(brand_tokens).split()).lower()
        detected_product = " ".join("".join(product_tokens).split()).lower()

        # 🛡️ FILTER DUPLIKASI KATA BERURUTAN (ex: "wardah wardah" -> "wardah")
        def clean_duplicate(text):
            parts = text.split()
            if len(parts) > 1 and parts[0] == parts[1]:
                return parts[0]
            return text

        detected_brand = clean_duplicate(detected_brand)
        detected_product = clean_duplicate(detected_product)

        # Final Cleanup
        if len(detected_brand) <= 2 or detected_brand in self.ignored_words:
            detected_brand = "unknown"
        if len(detected_product) <= 2:
            detected_product = "unknown"

        return detected_brand, detected_product

    def predict_sentiment_bilstm(self, text: str) -> str:
        if not text.strip():
            return "neutral"
            
        try:
            
            sequences = self.lstm_tokenizer.texts_to_sequences([text])
            padded = pad_sequences(sequences, maxlen=self.maxlen, padding='pre', truncating='pre')
            
            prediction = self.lstm_model.predict(padded, verbose=0)
            predicted_idx = int(np.argmax(prediction[0]))
            
            # Sesuaikan urutan dengan LabelEncoder saat training: 
            # Jika 0=negative, 1=neutral, 2=positive
            labels = ["negative", "neutral", "positive"]
            
            return labels[predicted_idx]
            
        except Exception as e:
            print(f"⚠️ Gagal prediksi: {e}")
            return "neutral"

# Inisialisasi Handler secara Global
models_prediction = ModelPredictor()
