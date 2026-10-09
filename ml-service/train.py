import pandas as pd
import numpy as np
from sklearn.metrics.pairwise import cosine_similarity
import joblib
import os

# Load data from Laravel storage
csv_path = '../storage/app/training_data.csv'
if not os.path.exists(csv_path):
    print(f"Training data not found at {csv_path}. Run 'php artisan ml:export-training-data' first.")
    exit(1)

data = pd.read_csv(csv_path)
print(f"Loaded {len(data)} borrowing records")
print(f"Unique students: {data['student_id'].nunique()}")
print(f"Unique books: {data['book_id'].nunique()}")

# Create user-item matrix
user_item_matrix = data.pivot_table(
    index='student_id',
    columns='book_id',
    values='borrows',
    fill_value=0
)

# Calculate item-item similarity
item_similarity = cosine_similarity(user_item_matrix.T)
item_similarity_df = pd.DataFrame(
    item_similarity,
    index=user_item_matrix.columns,
    columns=user_item_matrix.columns
)

# Save model
joblib.dump(item_similarity_df, '../storage/app/recommendation_model.pkl')
print("Model saved to storage/app/recommendation_model.pkl")