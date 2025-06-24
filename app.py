# erp_dashboard_streamlit.py
import streamlit as st
import pandas as pd
import numpy as np
import mysql.connector
import plotly.express as px

# --- MySQL CONNECTION ---
conn = mysql.connector.connect(
    host="localhost",
    user="root",
    passworder_id="",
    database="e-pharm1"
)

# Page configuration
st.set_page_config(layout="wide")
st.title("📦 ERP Monitoring Dashboard")

# Sidebar menu
menu = st.sidebar.selectbox(
    "Select Menu",
    [
        "📊 Inventory Monitoring",
        "🚚 Supplier Performance",
        "🚨 Alerts Center",
        "🤖 ML Recommendations",
        "🛡️ Safety Stock Calculator"
    ]
)

# Data loading function
@st.cache_data
def load_data():
    products = pd.read_sql_query("SELECT * FROM products", conn)
    warehouse = pd.read_sql_query("SELECT * FROM warehouse", conn)

    try:
        suppliers = pd.read_sql_query("SELECT * FROM suppliers", conn)
    except:
        suppliers = pd.DataFrame()
    
    try:
        po = pd.read_sql_query("SELECT * FROM purchase_order_ids", conn)
        poi = pd.read_sql_query("SELECT * FROM purchase_order_id_items", conn)
    except:
        po, poi = pd.DataFrame(), pd.DataFrame()

    try:
        so = pd.read_sql_query("SELECT * FROM sales_order_ids", conn)
        soi = pd.read_sql_query("SELECT * FROM sales_order_id_items", conn)
    except:
        so, soi = pd.DataFrame(), pd.DataFrame()

    try:
        tx = pd.read_sql_query("SELECT * FROM inventory_transactions", conn)
    except:
        tx = pd.DataFrame()

    return products, warehouse, suppliers, po, poi, so, soi, tx

# Load all data
products, warehouse, suppliers, po, poi, so, soi, tx = load_data()

# --- 1. Inventory Monitoring ---
if menu == "📊 Inventory Monitoring":
    st.subheader("📦 Inventory Monitoring per Branch")
    df = warehouse.merge(products[['code', 'stok_minimum']], on='code', how='left')
    branch_list = sorted(df['cabang'].dropna().unique())
    branch_select = st.selectbox("Select Branch:", branch_list)

    df_branch = df[df['cabang'] == branch_select].copy()
    df_branch['Status'] = df_branch.apply(
        lambda x: "🟢 Safe" if x['jumlah'] >= x['stok_minimum'] else "🔴 Restock Needed", axis=1
    )

    st.dataframe(df_branch[['code', 'namabarang', 'kategori', 'jumlah', 'stok_minimum', 'Status']])

    fig = px.bar(
        df_branch,
        x='namabarang',
        y='jumlah',
        color='Status',
        title='Stock per Branch',
        labels={'jumlah': 'Stock Quantity', 'namabarang': 'Product Name'}
    )
    st.plotly_chart(fig, use_container_width=True)

# --- 2. Supplier Performance ---
elif menu == "🚚 Supplier Performance":
    st.subheader("🚚 Supplier Performance")
    if not po.empty and not suppliers.empty:
        po_perf = po.merge(suppliers, on='id_supplier', how='left')
        po_perf['tanggal_pesan'] = pd.to_datetime(po_perf['tanggal_pesan'], errors='coerce')
        po_perf['tanggal_terima'] = pd.to_datetime(po_perf['tanggal_terima'], errors='coerce')
        po_perf['lead_time'] = (po_perf['tanggal_terima'] - po_perf['tanggal_pesan']).dt.days

        df_perf = po_perf.groupby('nama_supplier').agg({
            'lead_time': ['mean', 'count']
        }).reset_index()
        df_perf.columns = ['Supplier', 'Avg Lead Time (days)', 'Total order_ids']

        st.dataframe(df_perf)

        fig2 = px.bar(
            df_perf,
            x='Supplier',
            y='Avg Lead Time (days)',
            color='Supplier',
            title='Average Supplier Lead Time'
        )
        st.plotly_chart(fig2, use_container_width=True)
    else:
        st.warning("Supplier or purchase order_id data not available.")

# --- 3. Alerts Center ---
elif menu == "🚨 Alerts Center":
    st.subheader("🚨 Alerts: Products Below Minimum Stock")
    alert_df = warehouse.merge(products[['code', 'stok_minimum']], on='code', how='left')
    alert_df = alert_df[alert_df['jumlah'] < alert_df['stok_minimum']]
    if alert_df.empty:
        st.success("All products are in safe stock levels.")
    else:
        st.dataframe(alert_df[['code', 'namabarang', 'jumlah', 'stok_minimum', 'cabang']])

# --- 4. ML Recommendations ---
elif menu == "🤖 ML Recommendations":
    st.subheader("🤖 Product Recommendations Based on Sales Prediction")
    if not soi.empty:
        sales_sum = soi.groupby('code').agg({'jumlah': 'sum'}).reset_index()
        sales_sum = sales_sum.merge(products[['code']], on='code', how='left')
        sales_sum['next_week_prediction'] = (sales_sum['jumlah'] / 12).round().astype(int)

        recommendation = warehouse.merge(
            sales_sum[['code', 'next_week_prediction']],
            on='code',
            how='left'
        )
        recommendation['next_week_prediction'].fillna(0, inplace=True)
        recommendation['Restock Needed'] = recommendation['jumlah'] < recommendation['next_week_prediction']

        st.dataframe(recommendation[['code', 'namabarang', 'jumlah', 'next_week_prediction', 'Restock Needed']])
    else:
        st.warning("Sales data not available.")

# --- 5. Safety Stock Calculator ---
elif menu == "🛡️ Safety Stock Calculator":
    st.subheader("🛡️ Safety Stock Calculation")
    safety_df = products.copy()
    z = 1.65  # service level 95%
    safety_df['Safety Stock'] = (
        z * np.sqrt(safety_df['deviasi_demand']**2 + safety_df['deviasi_lead_time']**2)
    ).round().astype(int)
    st.dataframe(safety_df[['code', 'namabarang', 'deviasi_demand', 'deviasi_lead_time', 'Safety Stock']])
