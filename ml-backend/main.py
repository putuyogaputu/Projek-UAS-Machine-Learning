from fastapi import FastAPI, HTTPException
from pydantic import BaseModel
import pandas as pd
import numpy as np
from sklearn.linear_model import LinearRegression
from datetime import datetime
import os

app = FastAPI()

class DateRange(BaseModel):
    start_date: str
    end_date: str

@app.post("/predict")
def predict_stock(date_range: DateRange):
    try:
        # 1. Pastikan nama file benar-benar sesuai dengan yang ada di foldermu
        file_name = 'BBCAJK_5y_1d.csv'
        
        if not os.path.exists(file_name):
            raise Exception(f"File {file_name} tidak ditemukan di folder ml-backend.")

        # 2. Load Data
        df = pd.read_csv(file_name)
        
        # 3. Preprocessing (Bersihkan data kosong)
        df['Date'] = pd.to_datetime(df['Date'])
        df = df.dropna(subset=['Date', 'Close']) 

        df['Date_Ordinal'] = df['Date'].map(datetime.toordinal)
        X = df[['Date_Ordinal']]
        y = df['Close']

        # 4. Train Model
        model = LinearRegression()
        model.fit(X, y)

        # 5. Cek Tanggal Input User
        start_dt = pd.to_datetime(date_range.start_date)
        end_dt = pd.to_datetime(date_range.end_date)
        
        if end_dt < start_dt:
            raise Exception("Logika Error: Tanggal Akhir tidak boleh lebih lampau dari Tanggal Mulai.")

        # 6. Buat kerangka waktu yang direquest
        date_list = pd.date_range(start=start_dt, end=end_dt)
        df_request = pd.DataFrame({'Date': date_list})
        
        # 7. Ambil data Harga ASLI dari dataset (Garis Hijau)
        # Menggunakan merge agar tanggal yang bursa libur/kosong terisi NaN
        df_merged = pd.merge(df_request, df[['Date', 'Close']], on='Date', how='left')
        
        # 8. Lakukan PREDIKSI untuk seluruh tanggal tersebut (Garis Biru)
        date_ordinals = np.array([d.toordinal() for d in date_list]).reshape(-1, 1)
        predictions = model.predict(date_ordinals)

        # 9. Format Data untuk dikirim ke Laravel
        response_dates = [d.strftime('%Y-%m-%d') for d in date_list]
        
        # Ubah NaN (data kosong) menjadi None agar bisa dikonversi ke JSON oleh FastAPI
        actual_prices = df_merged['Close'].where(pd.notnull(df_merged['Close']), None).tolist()
        response_prices = [round(p, 2) for p in predictions]

        return {
            "dates": response_dates,
            "actual_prices": actual_prices,     # Data Asli
            "predicted_prices": response_prices # Data Prediksi (Tren ML)
        }
        
    except Exception as e:
        # Jika ada error, Python akan melempar pesan detailnya ke Laravel
        raise HTTPException(status_code=500, detail=str(e))