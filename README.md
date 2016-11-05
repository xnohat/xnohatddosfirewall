---------------------------------

	XNOHAT DDoS FIREWALL

	Version: 1.2

	xnohat@gmail.com

-----------------------------------

INSTRUCTION

Notes:

For CentOS only, modify yourself for other distros

/!\ Must do: Stop all Web/HTTPD Services and Database Services for free resources first

[!] Recommend: 
+ Using this firewall script on your reverse proxy
+ Block all traffic to your main (upstream) server except traffic from Reverse Proxy
+ Implement DNS Round robin with multiple reverse proxies to reduce DDoS load first
+ Contact ISP to upgrade your server BANDWIDTH, RAM, CPU, HDD to maximum of your budget first. I suggest: 4-8 Cores CPU, 16-32 GB RAM, 50 GB SSD, Port 1Gbps NIC Bandwidth

Step-by-step

$ yum install epel-release

$ yum install nload tmux

$ yum install php php-pdo

[+] Setting $logfile variable in logparser.php to path to your access.log

[+] Modify logparser.php to match your access log format ( we need parse "ip of request" and "time of request" ), especially time of request must change to match SQLite time format look like 2016-11-05 01:35:24
Example log line from nginx: 
180.93.103.169 - - [05/Nov/2016:03:19:12 +0700] "GET / HTTP/1.0" 200 148608 "-" "Mozilla/5.0 (Windows; U; Windows NT 6.1; en-US)" "-"

[+] Modify analyser.php, setting $threshold as number of request per $timewindow , any ip over this threshold will be 
block by iptables

To get right Threshold for DDoS attacking you, do step below

+ Use tcpdump to get packets attacking you $ tcpdump -vvvv -i <your_interface> -w <file_name.pcap> 
+ Open .pcap file in Wireshark:
	+ go to Edit -> Preference set:
		* "Show burst count for item rather than rate" set Enabled (check mark in the box)
		* set Burst rate resolution = Burst rate window size = 60000 miliseconds (1 min)
+ Go Statistic -> ipv4 -> all addresses: burst rate is number of packets per minute, use average numbers (just guess!) to Threshold

---------

$ chmod +x ./xnohatddosfirewall/runddosfirewall.sh

$ chmod 777 ./xnohatddosfirewall

Run script with sudo or root privilege: sudo ./runddosfirewall.sh
follow guide on screen

Press "Ctrl-b d" : to detach tmux

Re-attach tmux by command $ tmux attach -t 0

Start all your services again



CHANGE LOG:
* v1.1 : first release
* v1.2 : Implement new algorithm