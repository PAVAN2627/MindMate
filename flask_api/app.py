from flask import Flask, request, jsonify
import requests
from openai import AzureOpenAI          # new SDK (≥ 1.0.0)

app = Flask(__name__)

# ── Azure Text Analytics (sentiment) ───────────────────────────
AZURE_TA_ENDPOINT = "https://mindmate0281.cognitiveservices.azure.com/"
AZURE_TA_KEY      = "4LMsr8f0tH9ok9bazYRcOvvAtvia7UfBUzP8UFaAIks4UDZoI4pAJQQJ99BGACYeBjFXJ3w3AAAaACOGcLgY"

# ── Azure OpenAI config (new SDK) ──────────────────────────────
AZURE_OPENAI_ENDPOINT   = "https://pavan-mc0k5way-eastus2.cognitiveservices.azure.com/"
AZURE_OPENAI_KEY        = "9CkC9rlJmLiWj5HcAxzOlhPpSE4sCQtCNZrtYqPWIfbGAc6AdQGnJQQJ99BFACHYHv6XJ3w3AAAAACOGVPBZ"
AZURE_OPENAI_API_VERSION = "2024-12-01-preview"
AZURE_OPENAI_DEPLOYMENT  = "gpt-35-turbo"     # ← exact deployment name

client = AzureOpenAI(
    api_key       = AZURE_OPENAI_KEY,
    api_version   = AZURE_OPENAI_API_VERSION,
    azure_endpoint= AZURE_OPENAI_ENDPOINT
)

# ───────────────────────────────────────────────────────────────
@app.route("/analyze", methods=["POST"])
def analyze():
    """Return sentiment using Azure Text Analytics."""
    text = request.get_json().get("text", "")
    url  = AZURE_TA_ENDPOINT + "text/analytics/v3.0/sentiment"
    headers = {
        "Ocp-Apim-Subscription-Key": AZURE_TA_KEY,
        "Content-Type": "application/json"
    }
    body = {"documents": [{"id": "1", "language": "en", "text": text}]}

    r = requests.post(url, headers=headers, json=body, timeout=10)
    if r.status_code != 200:
        return jsonify({"error": "Text Analytics API error", "details": r.text}), 500

    sentiment = r.json()["documents"][0]["sentiment"]
    return jsonify({"sentiment": sentiment})

# ───────────────────────────────────────────────────────────────
@app.route("/generate_quote", methods=["POST"])
def generate_quote():
    """Return a short motivational quote tailored to mood."""
    mood = request.get_json().get("mood", "neutral").lower()

    prompt = (
        f"Give me one short motivational quote (≤ 25 words) for a person "
        f"who is feeling {mood}. Respond with the quote text only."
    )

    try:
        resp = client.chat.completions.create(
            model      = AZURE_OPENAI_DEPLOYMENT,   # deployment name
            messages   = [
                {"role": "system", "content": "You are a supportive mental-health assistant."},
                {"role": "user",   "content": prompt}
            ],
            temperature = 0.7,
            max_tokens  = 60
        )
        quote = resp.choices[0].message.content.strip().strip('"')
        return jsonify({"quote": quote})

    except Exception as e:
        # Log error for debugging
        print("⚠️  OpenAI API error:", e)
        return jsonify({
            "quote": "Every storm runs out of rain.",  # fallback
            "error": str(e)
        }), 500
 # ───────────────────────────────────────────────────────────────
@app.route("/summarize_moods", methods=["POST"])
def summarize_moods():
    """
    Input JSON  : { "lines": ["2025-07-01 : positive", "2025-07-02 : neutral", ...] }
    Output JSON : { "summary": "AI‑generated text ..." }
    """
    data  = request.get_json()
    lines = data.get("lines", [])

    if not lines:
        return jsonify({"error": "No mood data provided"}), 400

    mood_text = "\n".join(lines)
    prompt = (
        "You are an empathetic psychologist.\n"
        "Given these daily mood entries:\n"
        f"{mood_text}\n\n"
        "Write a brief 2‑3 sentence summary of the overall emotional trend "
        "and end with one practical tip to improve well‑being."
    )

    try:
        resp = client.chat.completions.create(
            model    = AZURE_OPENAI_DEPLOYMENT,
            messages = [
                {"role": "system", "content": "You summarize moods helpfully."},
                {"role": "user",   "content": prompt}
            ],
            temperature = 0.7,
            max_tokens  = 120
        )
        summary = resp.choices[0].message.content.strip()
        return jsonify({"summary": summary})

    except Exception as e:
        print("⚠️  OpenAI summary error:", e)
        return jsonify({"error": str(e)}), 500

# ───────────────────────────────────────────────────────────────
if __name__ == "__main__":
    app.run(host="127.0.0.1", port=5000, debug=True)
