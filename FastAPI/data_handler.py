# data_handler.py
import pandas as pd

class DataHandler:
    def __init__(self, file_path: str):
        self.file_path = file_path
        self.df = None
        self.load_dataset()

    def load_dataset(self):
        try:
            # GANTI: Menggunakan read_excel untuk membaca file .xlsx
            self.df = pd.read_excel(self.file_path)
            
            self.df["comment"] = self.df["comment"].fillna("")
            self.df["clean_comment"] = self.df["clean_comment"].fillna("")
                
            print(f"✅ Dataset Preprocessing Berhasil Dimuat: {len(self.df)} baris.")
        except Exception as e:
            print(f"❌ Gagal memuat dataset: {e}")
            self.df = pd.DataFrame(columns=["comment", "clean_comment", "video_url"])

data_manager = DataHandler("annotated_dataset.xlsx")