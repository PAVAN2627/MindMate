# 🧠 MindMate – AI-Powered Mental Wellness Tracker

MindMate is a web-based mental health companion that helps users track their emotional well-being using daily journal entries. It uses AI to analyze mood, generate motivational quotes, and provide supportive feedback and reports.

---

## 🌟 Features

- ✅ **User Registration & OTP Email Verification**
- 🔐 **Login & Session-based Authentication**
- 📓 **Daily Journal Entry with Mood Analysis (via Azure Cognitive Services)**
- 🤖 **Motivational Quote Generation (via Azure OpenAI GPT)**
- 📊 **Mood Chart Visualization (last 7 entries)**
- 📬 **Weekly Mood Report via Email (AI-summarized)**
- 📜 **Full Mood History**
- 👤 **User Profile (ID, Name, Email, Latest Mood)**
- 🧠 **Mood Booster Section**: Music, Comedy Videos, and Joke Generator
- 🔊 **Text-to-Speech Joke Reader (Azure TTS Integration)**

---

## 🛠️ Tech Stack

| Layer           | Technology                                  |
|----------------|----------------------------------------------|
| Frontend       | HTML, CSS, JavaScript, TailwindCSS           |
| Backend (Core) | PHP (with MySQL)                             |
| Database       | MySQL (`mindmate` DB)                        |
| AI Services    | Azure OpenAI (GPT-3.5/4), Azure Text Analytics |
| TTS            | Azure Cognitive Services – Text to Speech    |
| Charts         | Chart.js                                     |
| Email          | PHPMailer with Gmail SMTP                    |

---

## ⚙️ Setup Instructions

### 1. 🔧 Clone the Repo

```bash
git clone https://github.com/your-username/mindmate.git
cd mindmate

