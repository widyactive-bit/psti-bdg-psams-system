import os
# pyrefly: ignore [missing-import]
from fastapi import FastAPI, HTTPException
# pyrefly: ignore [missing-import]
from pydantic import BaseModel, Field
# pyrefly: ignore [missing-import]
from openai import OpenAI
# pyrefly: ignore [missing-import]
from dotenv import load_dotenv

# Load env variables
load_dotenv()

app = FastAPI(
    title="PSAMS AI Analytics Service",
    description="Layanan AI Analitik Kinerja Atlet Sepak Takraw PSTI Kota Bandung",
    version="1.0.0"
)

# Initialize OpenAI Client
# Gets API key from environment variable OPENAI_API_KEY
client = None
if os.getenv("OPENAI_API_KEY") and not os.getenv("OPENAI_API_KEY").startswith("sk-proj-placeholder"):
    try:
        client = OpenAI(api_key=os.getenv("OPENAI_API_KEY"))
    except Exception as e:
        print(f"Failed to initialize OpenAI client: {e}. AI service will run in fallback mock mode.")

class StatMetrics(BaseModel):
    tendangan: float = Field(..., ge=0, le=100)
    pukulan: float = Field(..., ge=0, le=100, description="Di Sepak Takraw, pukulan merujuk pada smash / block menggunakan dada/lengan terotentikasi")
    akurasi: float = Field(..., ge=0, le=100)
    kecepatan: float = Field(..., ge=0, le=100)
    endurance: float = Field(..., ge=0, le=100)
    agility: float = Field(..., ge=0, le=100)
    flexibility: float = Field(..., ge=0, le=100)
    strength: float = Field(..., ge=0, le=100)
    disiplin: float = Field(..., ge=0, le=100)
    fokus: float = Field(..., ge=0, le=100)
    leadership: float = Field(..., ge=0, le=100)

class AthleteData(BaseModel):
    nama: str
    nomor_induk_atlet: str
    klub: str
    posisi: str = Field("Killer (Smash)", description="Posisi sepak takraw: Tekong (Server), Feeder (Setter), atau Killer (Striker)")
    stats: StatMetrics
    prestasi_count: int = 0

@app.get("/")
def read_root():
    return {
        "status": "online",
        "service": "PSAMS AI Service",
        "sport": "Sepak Takraw (PSTI Kota Bandung)"
    }

@app.post("/analyze")
def analyze_athlete(athlete: AthleteData):
    """
    Menganalisis performa atlet sepak takraw berdasarkan metrik teknik, fisik, mental, dan prestasi.
    Menghasilkan narasi: Kelebihan, Kekurangan, Rekomendasi Latihan, dan Prediksi Prestasi.
    """
    
    # Calculate score using formula
    teknik_avg = (athlete.stats.tendangan + athlete.stats.pukulan + athlete.stats.akurasi + athlete.stats.kecepatan) / 4
    fisik_avg = (athlete.stats.endurance + athlete.stats.agility + athlete.stats.flexibility + athlete.stats.strength) / 4
    mental_avg = (athlete.stats.disiplin + athlete.stats.fokus + athlete.stats.leadership) / 3
    
    # Simple score based on achievement count: max out at 100
    prestasi_score = min(100.0, athlete.prestasi_count * 20.0)
    
    total_score = (teknik_avg * 0.4) + (fisik_avg * 0.3) + (mental_avg * 0.1) + (prestasi_score * 0.2)
    
    prompt = f"""
    Bertindaklah sebagai Pelatih Kepala Sepak Takraw Senior & Analis Olahraga PSTI Kota Bandung. 
    Lakukan analisis mendalam terhadap atlet sepak takraw berikut:
    
    Nama: {athlete.nama}
    Nomor Induk: {athlete.nomor_induk_atlet}
    Klub Asal: {athlete.klub}
    Posisi Utama: {athlete.posisi} (Pilihan posisi Sepak Takraw: Tekong/Server, Feeder/Umpan, Killer/Smash)
    Jumlah Prestasi Tercatat: {athlete.prestasi_count} turnamen
    
    Skor Evaluasi Atlet (Skala 0 - 100):
    - Teknik (Tendangan: {athlete.stats.tendangan}, Smash/Blok: {athlete.stats.pukulan}, Akurasi: {athlete.stats.akurasi}, Kecepatan: {athlete.stats.kecepatan})
    - Fisik (Endurance: {athlete.stats.endurance}, Agility: {athlete.stats.agility}, Flexibility: {athlete.stats.flexibility}, Strength: {athlete.stats.strength})
    - Mental (Disiplin: {athlete.stats.disiplin}, Fokus: {athlete.stats.fokus}, Leadership: {athlete.stats.leadership})
    
    Skor Terhitung Formula Sistem Ranking PSAMS: {total_score:.2f} / 100.
    
    Berikan laporan analitik dalam format bahasa Indonesia formal dengan 4 bagian utama berikut:
    1. KELEBIHAN UTAMA ATLET: Analisis kekuatan teknik dan fisik terkait posisinya (misal: fleksibilitas tinggi untuk roll spike, servis tekong akurat, kelincahan feeder).
    2. KEKURANGAN UTAMA ATLET: Identifikasi kelemahan spesifik (misal: kekuatan smash menurun di set ketiga karena stamina, atau fokus mental saat tertinggal poin).
    3. REKOMENDASI PROGRAM LATIHAN: Program latihan tertarget (misal: plyometrics, stretching untuk kelenturan kaki, latihan akurasi servis tekong).
    4. PREDIKSI PRESTASI MENDATANG: Proyeksi keberhasilan atlet di kejuaraan daerah (Kejurda) atau nasional (Kejurnas) berdasarkan statistik mental dan fisik saat ini.
    
    Pastikan menggunakan istilah-istilah resmi sepak takraw seperti 'tekong', 'feeder', 'killer', 'roll spike', 'servis mula', dan 'sepak sila'.
    """

    # If OpenAI API Key is not set or client fails, fallback to rule-based mock analysis
    if client is None:
        return get_mock_analysis(athlete, total_score, teknik_avg, fisik_avg, mental_avg)

    try:
        response = client.chat.completions.create(
            model="gpt-3.5-turbo",
            messages=[
                {"role": "system", "content": "Anda adalah analis data dan pelatih sepak takraw senior profesional dari PSTI Kota Bandung."},
                {"role": "user", "content": prompt}
            ],
            temperature=0.7,
            max_tokens=1000
        )
        narration = response.choices[0].message.content
        return {
            "nomor_induk_atlet": athlete.nomor_induk_atlet,
            "nama": athlete.nama,
            "total_score": round(total_score, 2),
            "engine": "OpenAI GPT-3.5",
            "analysis": narration
        }
    except Exception as e:
        print(f"Error calling OpenAI API: {e}. Falling back to system analytics.")
        return get_mock_analysis(athlete, total_score, teknik_avg, fisik_avg, mental_avg)


def get_mock_analysis(athlete: AthleteData, total_score: float, teknik: float, fisik: float, mental: float):
    # Rule-based system fallback generator
    kelebihan = []
    kekurangan = []
    rekomendasi = []
    prediksi = ""

    # Kelebihan analysis
    if athlete.stats.flexibility > 80:
        kelebihan.append("Kelenturan tubuh luar biasa (flexibility), sangat mendukung gerakan salto (roll spike) maupun hadangan di jaring.")
    if athlete.stats.tendangan > 80:
        kelebihan.append("Kekuatan tendangan (sepak sila/sepak kura) sangat konsisten dan bertenaga.")
    if athlete.stats.akurasi > 80:
        kelebihan.append("Akurasi penempatan bola sangat baik, menempatkan servis tekong atau umpan feeder tepat di area pertahanan lawan yang kosong.")
    if not kelebihan:
        kelebihan.append("Stabilitas performa dasar yang cukup merata di semua sektor taktis.")

    # Kekurangan analysis
    if athlete.stats.endurance < 70:
        kekurangan.append("Daya tahan fisik (endurance) menurun di set penentu (rubber set), menyebabkan akurasi umpan dan loncatan berkurang.")
    if athlete.stats.fokus < 75:
        kekurangan.append("Fokus mental sering terganggu ketika berada di bawah tekanan servis lawan beruntun.")
    if athlete.stats.strength < 70:
        kekurangan.append("Kekuatan otot kaki (strength) perlu ditingkatkan untuk menghasilkan daya ledak lompatan smash yang lebih tinggi.")
    if not kekurangan:
        kekurangan.append("Konsistensi ritme bertanding yang perlu diuji menghadapi tim-tim unggulan luar daerah.")

    # Rekomendasi program
    if athlete.stats.flexibility < 75:
        rekomendasi.append("Latihan peregangan dinamis dan yoga untuk meningkatkan jangkauan tendangan sila.")
    if athlete.stats.endurance < 75:
        rekomendasi.append("Program latihan kardiovaskular terstruktur (interval running 3x seminggu) untuk menjaga stamina.")
    if athlete.stats.akurasi < 80:
        rekomendasi.append("Latihan target cone di lapangan untuk melatih ketepatan arah bola tekong/feeder.")
    rekomendasi.append("Simulasi tanding format liga internal PSTI Kota Bandung untuk mengasah mental kompetisi.")

    # Prediksi
    if total_score > 80:
        prediksi = "Dengan skor analitik saat ini, atlet diprediksi kuat mampu memperebutkan medali emas pada Kejuaraan Daerah (Kejurda) Jawa Barat berikutnya."
    elif total_score > 65:
        prediksi = "Atlet memiliki potensi besar untuk menembus babak semifinal tingkat provinsi. Fokus perbaikan fisik pada endurance akan menjadi faktor kunci kenaikan prestasi."
    else:
        prediksi = "Atlet membutuhkan waktu pembinaan minimal 6-12 bulan dengan fokus program latihan teknik dasar sepak sila sebelum direkomendasikan masuk tim utama kota."

    analysis_narration = f"""1. KELEBIHAN UTAMA ATLET:
- {" ".join(kelebihan)}

2. KEKURANGAN UTAMA ATLET:
- {" ".join(kekurangan)}

3. REKOMENDASI PROGRAM LATIHAN:
- {", ".join(rekomendasi)}

4. PREDIKSI PRESTASI MENDATANG:
{prediksi}"""

    return {
        "nomor_induk_atlet": athlete.nomor_induk_atlet,
        "nama": athlete.nama,
        "total_score": round(total_score, 2),
        "engine": "PSAMS Rule Engine (Offline Fallback)",
        "analysis": analysis_narration
    }
