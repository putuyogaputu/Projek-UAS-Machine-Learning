from fastapi import FastAPI, HTTPException
from pydantic import BaseModel
import pandas as pd
import numpy as np
import joblib
import os
import math # Tambahan untuk ngecek NaN dan Infinity
from datetime import datetime

app = FastAPI()

class DateRange(BaseModel):
    start_date: str
    end_date: str

# ==============================================================
# FUNGSI KEAMANAN JSON (MENCEGAH CRASH)
# ==============================================================
def safe_float(value):
    """Mengubah nilai apapun menjadi float yang sah atau None (null JSON)"""
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
        file_name = 'BBCAJK_5y_1d.csv'
        
        if not os.path.exists(file_name):
            raise Exception(f"File dataset {file_name} tidak ditemukan.")

        df = pd.read_csv(file_name)
        df['Date'] = pd.to_datetime(df['Date'], utc=True).dt.tz_localize(None).dt.normalize()
        df = df.dropna(subset=['Date', 'Close']) 

        start_dt = pd.to_datetime(date_range.start_date)
        end_dt = pd.to_datetime(date_range.end_date)
        
        if end_dt < start_dt:
            raise Exception("Tanggal Akhir tidak boleh lebih lampau dari Tanggal Mulai.")

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
        
        # Ambil harga masa lalu (Dibersihkan pakai safe_float)
        if len(history_df) >= 2:
            last_2_prices = history_df['Close'].tail(2).tolist()
            current_dua_hari_lalu = safe_float(last_2_prices[0]) or 0.0
            current_kemarin = safe_float(last_2_prices[1]) or 0.0
        else:
            current_dua_hari_lalu = safe_float(df['Close'].iloc[0]) or 0.0
            current_kemarin = safe_float(df['Close'].iloc[0]) or 0.0

        for d in date_list:
            X_input = pd.DataFrame({
                'Harga_Kemarin': [current_kemarin],
                'Harga_Dua_Hari_Lalu': [current_dua_hari_lalu]
            })
            
            try:
                pred_val = model.predict(X_input)[0]
            except ValueError:
                X_input = pd.DataFrame({
                    'Harga_Dua_Hari_Lalu': [current_dua_hari_lalu],
                    'Harga_Kemarin': [current_kemarin]
                })
                pred_val = model.predict(X_input)[0]
                
            # BERSIHKAN HASIL PREDIKSI SEBELUM DISIMPAN
            clean_pred = safe_float(pred_val)
            
            # Jika tebakan model rusak, gunakan harga kemarin agar grafik tidak putus
            if clean_pred is None:
                clean_pred = current_kemarin
                
            predicted_prices.append(clean_pred)
            
            actual_row = df[df['Date'] == d]
            if not actual_row.empty and pd.notna(actual_row['Close'].values[0]):
                today_price = safe_float(actual_row['Close'].values[0])
            else:
                today_price = clean_pred
                
            current_dua_hari_lalu = current_kemarin
            current_kemarin = today_price

        # ==============================================================
        
        response_dates = [d.strftime('%Y-%m-%d') for d in date_list]
        
        # Bersihkan seluruh harga asli menggunakan safe_float
        clean_actual_prices = [safe_float(x) for x in df_merged['Close']]

        return {
            "dates": response_dates,
            "actual_prices": clean_actual_prices, # 100% Bebas NaN
            "predicted_prices": predicted_prices  # 100% Bebas NaN
        }
        
    except Exception as e:
        raise HTTPException(status_code=500, detail=str(e))