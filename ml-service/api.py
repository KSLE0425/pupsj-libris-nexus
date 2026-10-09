from fastapi import FastAPI, HTTPException
from pydantic import BaseModel
import joblib
import pandas as pd
import numpy as np
import os

app = FastAPI(title="Library Recommendation API")

# Path to the trained model
model_path = '../storage/app/recommendation_model.pkl'

# Try to load the model if it exists
model = None
if os.path.exists(model_path):
    try:
        model = joblib.load(model_path)
        print("Model loaded successfully")
    except Exception as e:
        print(f"Error loading model: {e}")
else:
    print(f"Model not found at {model_path}. Run train.py first.")

class RecommendRequest(BaseModel):
    student_id: int
    top_n: int = 5

@app.get("/")
def root():
    return {"message": "Library Recommendation API is running"}

@app.post("/recommend")
def recommend(request: RecommendRequest):
    if model is None:
        # Fallback: return popular book IDs (1 to 5)
        return {"recommendations": [1, 2, 3, 4, 5]}
    
    # Simple popularity-based fallback using the model (if it's a similarity matrix)
    # For now, return the first top_n book IDs from the model columns
    try:
        # If model is a DataFrame (item-item similarity), take its columns
        if hasattr(model, 'columns'):
            book_ids = model.columns.tolist()
        else:
            book_ids = list(range(1, 21))  # fallback IDs
        recommendations = book_ids[:request.top_n]
        return {"recommendations": recommendations}
    except Exception as e:
        print(f"Recommendation error: {e}")
        return {"recommendations": [1, 2, 3, 4, 5]}