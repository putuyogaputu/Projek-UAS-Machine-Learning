from fastapi import FastAPI, HTTPException
from pydantic import BaseModel
import pandas as pd
import numpy as np
import joblib
import os
import math
import warnings
from datetime import datetime

app = FastAPI()

class DateRange(BaseModel):
    start_date: str
    end_date: str

# Fungsi Satpam JSON
def safe_float(value):
    if pd.isna(value) or value is None:
        return None
    try:
        f_val = float(value)
        if math.isnan(f_val) or math.isinf(f_val):
            return None
        return round(f_val, 2)
    except:
        return None

@app.post("/predict")
def predict_stock(date_range: DateRange):
    try:
        # Sesuaikan dengan nama file CSV kamu (saya buat agar bisa mendeteksi ada titik atau tidak)
        file_name = 'BBCAJK_5y_1d.csv'
        if not os.path.exists(file_name):
            file_name = 'BBCA.JK_5y_1d.csv'
            if not os.path.exists(file_name):
                raise Exception(f"File dataset tidak ditemukan.")

        df = pd.read_csv(file_name)
        
        # ==============================================================
        # PERBAIKAN BUG: TIMEZONE SHIFT
        # Memotong string "2025-08-15 00:00:00+07:00" menjadi "2025-08-15" saja 
        # agar tidak mundur 1 hari akibat konversi UTC
        # ==============================================================
        df['Date'] = pd.to_datetime(df['Date'].astype(str).str[:10])
        
        # Urutkan data berdasarkan tanggal
        df = df.dropna(subset=['Date', 'Close']).sort_values('Date').reset_index(drop=True) 

        start_dt = pd.to_datetime(date_range.start_date)
        end_dt = pd.to_datetime(date_range.end_date)
        
        if end_dt < start_dt:
            raise Exception("Tanggal Akhir tidak boleh lebih lampau dari Tanggal Mulai.")

        if len(df) < 3:
            raise Exception("Dataset terlalu pendek. Minimal harus ada 3 hari bursa.")
            
        min_start_date = df['Date'].iloc[2]
        max_end_date = df['Date'].max() # Sekarang ini akan menghasilkan 15 Agustus 2025 yang sesungguhnya!
        
        if start_dt < min_start_date:
            raise Exception(f"Gagal! Tanggal Mulai minimal adalah {min_start_date.strftime('%d %B %Y')} karena model AI memerlukan data 2 hari bursa sebelumnya untuk mulai bekerja.")
            
        if end_dt > max_end_date:
            raise Exception(f"Gagal! Tanggal Akhir maksimal adalah {max_end_date.strftime('%d %B %Y')} karena data referensi pada dataset hanya tersedia sampai tanggal tersebut.")

        date_list = pd.date_range(start=start_dt, end=end_dt)
        df_request = pd.DataFrame({'Date': date_list})
        df_merged = pd.merge(df_request, df[['Date', 'Close']], on='Date', how='left')
        
        model_path = 'model_prediksi_saham.pkl'
        
        if os.path.exists(model_path):
            model = joblib.load(model_path)
        else:
            raise Exception(f"File model {model_path} tidak ditemukan.")

        predicted_prices = []
        history_df = df[df['Date'] < start_dt].sort_values('Date')
        
        if len(history_df) >= 2:
            last_2_prices = history_df['Close'].tail(2).tolist()
            current_dua_hari_lalu = safe_float(last_2_prices[0]) or 0.0
            current_kemarin = safe_float(last_2_prices[1]) or 0.0
        else:
            current_dua_hari_lalu = safe_float(df['Close'].iloc[0]) or 0.0
            current_kemarin = safe_float(df['Close'].iloc[0]) or 0.0

        if hasattr(model, 'feature_names_in_'):
            features = [f.lower() for f in model.feature_names_in_]
        else:
            features = ['harga_kemarin', 'harga_dua_hari_lalu']

        warnings.filterwarnings("ignore", category=UserWarning)

        historical_dict = {d.strftime('%Y-%m-%d'): val for d, val in zip(df['Date'], df['Close'])}

        for d in date_list:
            input_dict = {
                'harga_kemarin': current_kemarin,
                'harga_dua_hari_lalu': current_dua_hari_lalu
            }
            
            row_data = [input_dict.get(f, 0.0) for f in features]
            pred_val = model.predict([row_data])[0]
            
            clean_pred = safe_float(pred_val)
            if clean_pred is None: clean_pred = current_kemarin
            predicted_prices.append(clean_pred)
            
            d_str = d.strftime('%Y-%m-%d')
            actual_price = historical_dict.get(d_str)
            
            if actual_price is not None and pd.notna(actual_price):
                today_price = safe_float(actual_price)
            else:
                today_price = clean_pred
                
            current_dua_hari_lalu = current_kemarin
            current_kemarin = today_price

        response_dates = [d.strftime('%Y-%m-%d') for d in date_list]
        clean_actual_prices = [safe_float(x) for x in df_merged['Close']]

        return {
            "dates": response_dates,
            "actual_prices": clean_actual_prices,
            "predicted_prices": predicted_prices 
        }
        
    except Exception as e:
        raise HTTPException(status_code=500, detail=str(e))