# 📬 Mail Server Monitor

A PHP script to monitor your mail server end-to-end by sending a test email and verifying its delivery via IMAP — including hash validation to ensure content integrity. If anything fails, an alert is sent via [BulkSMS](https://www.bulksms.co.uk/).

If like me, you run or are responsible for running your own mail server ether at home or in the cloud. You will know how paronoid you can get that it might not be working correctly. Most of us use services such as PRTG or Uptimekuma to monitor the servers uptime. But this does not perform a sending and receiving test. 

This script acts as simple and regular test to check that you can send and receive correctly on your server. You don't need to send the email from the same account you are receiving, but it is probably a good idea to ether do this or run the script twice to check that your server can send and receive mail correctly.

I wrote this to include an alert via SMS, for this I am using [BulkSMS](https://www.bulksms.co.uk/) as this already is used by my PRTG server and it is very easy to use and cheap. Feel free to edit the script to alert in another way if you need to, but I don't think it is reliable to to alert to a mail server problem using the same mail server.

---

## ✨ Features

- Sends a test email using **PHPMailer**
- Checks for delivery using **IMAP**
- Verifies the body using a unique **hash**
- Sends **SMS alerts** if:
  - Email sending fails
  - Delivery fails
  - Content mismatch occurs
- Works on **Linux** and **Windows**

---

## 📦 Requirements

- PHP 7.2+ (8.0+ recommended)
- PHP `imap` and `curl` extensions
- PHPMailer (included via Composer or manually)
- A mailbox with IMAP access
- A BulkSMS account for alerting

---

## 🚀 Installation

1. Clone this repo:

   ```bash
   git clone https://github.com/johnhart96/mail-server-monitor.git
   cd mail-server-monitor
   sudo bash install.sh
