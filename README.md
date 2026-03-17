# MP4 to MP3 Converter

A simple web-based application that converts MP4 video files into MP3 audio using PHP and FFmpeg. Built for deployment on a Linux server with Apache.

---

## 🚀 Features

* Upload MP4 files through a web interface
* Convert video files to MP3 format
* Download converted audio files
* Automatic cleanup of old conversion jobs (cron-based)
* Lightweight and easy to deploy

---

## 🛠 Tech Stack

* PHP
* FFmpeg
* Apache (or any compatible web server)
* Linux (tested on Debian-based systems)

---

## 📁 Project Structure

```
converter1/
│
├── index.php            # Main UI
├── convert.php          # Handles upload and conversion
├── cleanup.sh           # Cron-based cleanup script
├── cleanup.php          # Optional PHP cleanup script
├── jobs/                # Temporary job storage (ignored in Git)
├── .gitignore
└── README.md
```

---

## ⚙️ Installation

### 1. Install dependencies

```bash
sudo apt update
sudo apt install -y apache2 php ffmpeg
```

---

### 2. Clone the repository

```bash
git clone https://github.com/YOUR_USERNAME/mp4converter.git
cd mp4converter
```

---

### 3. Set permissions

```bash
sudo chown -R www-data:www-data /var/www/html/mp4converter
sudo chmod -R 755 /var/www/html/mp4converter
```

---

### 4. Access the app

Open your browser and go to:

```
http://YOUR_SERVER_IP/mp4converter
```

---

## 🧹 Automatic Cleanup (Recommended)

To prevent storage from filling up, a cron job deletes old conversion jobs.

### Setup cron (runs every hour):

```bash
sudo crontab -u www-data -e
```

Add:

```bash
0 * * * * /var/www/html/mp4converter/cleanup.sh >> /var/www/html/mp4converter/cleanup.log 2>&1
```

---

## ⚠️ Security Notes

* Ensure proper file permissions for the `jobs/` directory
* Avoid exposing `cleanup.php` publicly without protection
* Consider limiting upload file size in PHP configuration
* Use HTTPS in production environments

---

## 🧠 How It Works

1. User uploads an MP4 file
2. The server creates a unique job directory
3. FFmpeg converts the file to MP3
4. The output file is saved in `/jobs/<job_id>/`
5. User downloads the converted file
6. Old jobs are periodically deleted by the cleanup script

---

## 🔮 Future Improvements

* Delete files automatically after download
* Add upload progress bar
* Support additional formats (e.g., WAV, AAC)
* Queue system for handling multiple conversions
* Docker support for easier deployment

---

## 📄 License

This project is open-source and available under the MIT License.

---

## 👨‍💻 Author

Jason
