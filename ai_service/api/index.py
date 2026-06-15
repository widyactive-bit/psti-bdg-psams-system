import os
import sys

# Tambahkan direktori root proyek ke sys.path agar impor lokal main.py berjalan aman
sys.path.append(os.path.dirname(os.path.dirname(os.path.abspath(__file__))))

from main import app
