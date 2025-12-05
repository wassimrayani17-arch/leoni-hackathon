import smtplib
from email.message import EmailMessage
import pandas as pd

# Gmail SMTP Settings
SMTP_SERVER = "smtp.gmail.com"
SMTP_PORT = 465  # SSL port
SENDER_EMAIL = "sender-email@gmail.com"  # Your Gmail address
SENDER_PASSWORD = "yuuo bnlt jobt ovoy"  # From Step 1

def send_stock_alert(item_name, current_qty, reorder_point, recipient_email):
    """Send low-stock alert via Gmail."""
    msg = EmailMessage()
    msg["Subject"] = f"⚠️ Low Stock Alert: {item_name}"
    msg["From"] = SENDER_EMAIL
    msg["To"] = recipient_email
    
    msg.set_content(f"""
    Inventory Warning:
    - Item: {item_name}
    - Current Quantity: {current_qty}
    - Reorder Point: {reorder_point}
    """)

    # Send email securely
    with smtplib.SMTP_SSL(SMTP_SERVER, SMTP_PORT) as server:
        server.login(SENDER_EMAIL, SENDER_PASSWORD)
        server.send_message(msg)
    print(f"Alert sent for {item_name} to {recipient_email}")

def check_inventory(df, recipient_email="recipient_email@gmail.com"):
    """Check all items and send alerts if stock is low."""
    for _, row in df.iterrows():
        if row["Quantity"] < row["Reorder_Point"]:
            send_stock_alert(
                item_name=row["Item_Name"],
                current_qty=row["Quantity"],
                reorder_point=row["Reorder_Point"],
                recipient_email=recipient_email
            )

# Load your inventory data
inventory_df = pd.read_csv("inventory_analysis_results.csv")  # Ensure this file exists
check_inventory(inventory_df)
