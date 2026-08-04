# 🚀 AI OPM & Coaching Automation - Future Product Roadmap

## Executive Overview
**Learmy / WhatsWay** is evolving into an autonomous **AI OPM (Online Program Management) Platform**. The platform automates coaching operations—from class scheduling and WhatsApp notifications to AI-driven live meeting hosting, auto-admit gatekeeping, real-time audio transcription, automated class notes generation, and doubt resolution.

---

## 🏗 System Architecture Diagram

```
                               ┌────────────────────────────────┐
                               │       AI OPM MASTER BRAIN      │
                               └───────────────┬────────────────┘
                                               │
    ┌──────────────────┬───────────────────────┼───────────────────────┬───────────────────┐
    ▼                  ▼                       ▼                       ▼                   ▼
 [Phase 0]          [Phase 1]               [Phase 2]               [Phase 3]           [Phase 4]
 Current Setup     AI Meeting Bot          AI Notes Maker          In-Meeting Chat     AI OPM Operations
 • Meet Auto-Gen   • Auto Call Host        • Speech-to-Text        • Realtime RAG      • Fee Reminders
 • WhatsApp Alert  • Guest Auto-Admit      • AI Notes PDF          • Meet Chat Bot     • Dropout Alerts
 • Target Mapping  • Attendance Logging    • Auto WhatsApp Dispatch• Doubt Resolver   • Multi-Tenant SaaS
```

---

## ✅ Phase 0: Current System Foundation (Completed)
- [x] **Modular Feature Flags**: Centralized configuration control via [`config/all.php`](file:///Users/rohitk/data/solidrixtech/clients/whats/config/all.php) for instant toggling of modules (`messaging`, `contacts`, `inbox`, `automations`, `broadcasts`, `ai`, `social`, `leads`, `integrations`, `reports`, `developer`).
- [x] **1-Click Google OAuth & Calendar Integration**: Token refresh management via `WorkspaceGoogleToken`.
- [x] **Automated Google Meet Link Generation**: Meet URLs auto-created on class scheduling if manual link is omitted.
- [x] **Smart Target Mapping**: Grouping by Batches (`ContactTag`), Segments, and Individual Contacts.
- [x] **Automated WhatsApp Reminders**: CRON scheduler (`SendMeetingReminders.php`) delivering 1-hour and 15-minute meeting alerts.
- [x] **Responsive Classes UI**: List & Calendar dual view, standard dashboard container spacing, and 1-click Meet Link copy button.

---

## 🤖 Phase 1: Autonomous AI Meeting Bot & Gatekeeper
> **Goal:** Eliminate manual host intervention by allowing AI to start the meeting and manage student entry.

### Key Capabilities:
1. **Auto Call Start (Host Bot)**
   - Headless browser worker (`Playwright` / `Recall.ai` API) triggers 2 minutes prior to `start_time`.
   - Bot authenticates with workspace Google OAuth credentials and joins Meet as **Host/Organizer**.
   - Camera/Mic initialized in muted state to keep room active.

2. **Auto Admit & Guest List Management**
   - **Enrolled Roster Verification**: Resolves student emails/names from selected `ContactTag` or `Segment`.
   - **DOM Listener**: Listens to Google Meet join request popups (*"Someone wants to join this call"*).
   - **100ms Gatekeeper Action**:
     - **Enrolled Student**: Clicks **"Admit"** automatically.
     - **Outsider / Unknown**: Denies entry or flags alert.

3. **Automated Attendance Logging**
   - Logs exact entry & exit timestamps for every admitted student into database.
   - Generates downloadable CSV / Excel attendance report after class completion.

---

## 📝 Phase 2: AI Class Notes Maker & PDF Summarizer
> **Goal:** Automatically record, transcribe, summarize, and deliver structured study notes to students.

### Key Capabilities:
1. **Live Audio Streaming & Speech-to-Text**
   - Bot streams live class audio to high-precision ASR (OpenAI `whisper-1` / `Deepgram`).
   - Supports multilingual audio (English, Hindi, Hinglish).

2. **AI Synthesis Engine (Gemini / GPT-4o)**
   - Generates structured Markdown notes containing:
     - **Key Topics & Concepts Covered**
     - **Formulas, Definitions & Equations**
     - **Important Questions & Homework**
     - **Class Quiz MCQs**

3. **Automated PDF & WhatsApp Dispatch**
   - Converts Markdown notes to PDF (`dompdf` / `snappy`).
   - Dispatches notes PDF directly to the targeted batch/segment via WhatsApp Cloud API.

---

## 💬 Phase 3: In-Meeting Live AI Chat Support (AI Co-Host)
> **Goal:** Answer student doubts in real-time inside the Google Meet chat window during live classes.

### Key Capabilities:
1. **Google Meet Chat DOM Listener**
   - Monitors live chat messages in Google Meet (`div[data-message-text]`).

2. **Real-time RAG Knowledgebase Querying**
   - Matches student doubts against Qdrant Vector Store (trained on syllabus PDFs, textbooks, and past lecture notes).

3. **Live Chat Response Injection**
   - Bot types helpful replies in Meet chat within 2 seconds:
     > `🤖 AI Tutor: The formula for integration by parts is ∫u dv = uv - ∫v du.`

---

## 📊 Phase 4: Full AI OPM (Operations, Billing & Retention)
> **Goal:** Run complete coaching operations and scale revenue autonomously.

### Key Capabilities:
1. **Automated Fee Collection & Payment Links**
   - Sends monthly tuition fee reminders via WhatsApp with embedded payment gateway links (Razorpay, Stripe, MyFatoorah).
   - Auto-updates payment status upon webhook confirmation.

2. **Student Dropout Risk & Attendance Alerts**
   - Flags students who miss 2+ consecutive classes.
   - Automatically sends check-in WhatsApp messages to students and alert digests to parents/admin.

3. **Multi-Tenant White-Label Coaching SaaS**
   - Package Learmy / WhatsWay as a turnkey subscription SaaS for other coaching institutes, academies, and private tutors.

---

## 🛠 Proposed Tech Stack Matrix

| Layer | Recommended Technology |
|---|---|
| **Backend Core** | PHP 8.3 / Laravel 11 / Inertia.js / React |
| **Database & Cache** | MySQL 8.0 / Redis |
| **Meeting Bot Engine** | Playwright Node.js Worker / Recall.ai API |
| **Speech-to-Text (STT)** | OpenAI Whisper API / Deepgram |
| **AI LLM Models** | Google Gemini 1.5 Pro / OpenAI GPT-4o |
| **Vector Database** | Qdrant Vector Engine |
| **Messaging Channel** | WhatsApp Official Cloud API (Meta) |

---
*Created & Maintained for Learmy / WhatsWay Project Roadmap.*
