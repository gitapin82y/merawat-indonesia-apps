// payment-realtime.js
class PaymentStatusChecker {
    constructor(donationId, snapToken, options = {}) {
        this.donationId = donationId;
        this.snapToken = snapToken;
        this.options = {
            interval: options.interval || 3000, // Default 3 seconds
            maxAttempts: options.maxAttempts || 960, // Default 48 minutes (960 * 3 seconds)
            onSuccess: options.onSuccess || (() => {}),
            onExpired: options.onExpired || (() => {}),
            onFailed: options.onFailed || (() => {}),
            onChecking: options.onChecking || (() => {}),
            ...options
        };
        
        this.attempts = 0;
        this.isChecking = false;
        this.intervalId = null;
    }
    
   start() {
    this.checkStatus();
    this.scheduleNext();
    console.log('Payment status checker started');
}

scheduleNext() {
    const delay = this.getDelay(this.attempts);
    this.intervalId = setTimeout(() => {
        this.checkStatus().then(() => {
            if (this.intervalId !== null) this.scheduleNext();
        });
    }, delay);
}

getDelay(n) {
    if (n <= 3)  return 5000;
    if (n <= 6)  return 10000;
    if (n <= 10) return 20000;
    return 30000;
}

stop() {
    if (this.intervalId) {
        clearTimeout(this.intervalId); // ganti clearInterval → clearTimeout
        this.intervalId = null;
    }
}
    
  
    
    async checkStatus() {
        if (this.isChecking) return;
        
        this.isChecking = true;
        this.attempts++;
        
        try {
              this.options.onChecking(this.attempts, this.getDelay(this.attempts + 1));
            
            const response = await fetch(`/donations/check-status/${this.snapToken}`);
            const data = await response.json();
            
            if (data.success && data.data) {
                const status = data.data.status;
                
                switch (status) {
                    case 'PAID':
                        this.stop();
                        this.options.onSuccess(data.data);
                        setTimeout(() => window.location.reload(), 1000);
                        break;
                        
                    case 'EXPIRED':
                    case 'FAILED':
                        this.stop();
                        this.options.onExpired(data.data);
                        setTimeout(() => window.location.reload(), 2000);
                        break;
                        
                    default:
                        // Still pending, continue checking
                        break;
                }
            }
            
            // Stop after max attempts
            if (this.attempts >= this.options.maxAttempts) {
                this.stop();
                this.options.onExpired({ status: 'MAX_ATTEMPTS_REACHED' });
            }
            
        } catch (error) {
            console.error('Error checking payment status:', error);
        } finally {
            this.isChecking = false;
        }
    }
}

// Usage in status page
document.addEventListener('DOMContentLoaded', function() {
    if (typeof window.paymentConfig !== 'undefined') {
        const checker = new PaymentStatusChecker(
            window.paymentConfig.donationId,
            window.paymentConfig.snapToken,
            {
                interval: 3000,
                onChecking: function(attempts, nextDelay) {
    const statusElement = document.getElementById('status-check-info');
    if (statusElement) {
        statusElement.innerHTML = `
            <small class="text-muted">
                <i class="fa fa-sync-alt fa-spin me-1"></i>
                Memeriksa status... cek berikutnya ${nextDelay/1000} detik (percobaan ke-${attempts})
            </small>
        `;
    }
},
                onSuccess: function(data) {
                    const statusElement = document.getElementById('status-check-info');
                    if (statusElement) {
                        statusElement.innerHTML = `
                            <div class="alert alert-success text-center">
                                <i class="fa fa-check-circle me-2"></i>
                                Pembayaran berhasil! Mengalihkan halaman...
                            </div>
                        `;
                    }
                },
                onExpired: function(data) {
                    const statusElement = document.getElementById('status-check-info');
                    if (statusElement) {
                        statusElement.innerHTML = `
                            <div class="alert alert-danger text-center">
                                <i class="fa fa-times-circle me-2"></i>
                                Waktu pembayaran telah habis.
                            </div>
                        `;
                    }
                }
            }
        );
        
        checker.start();
     
        
        // Clean up on page unload
        window.addEventListener('beforeunload', function() {
            checker.stop();
        });
    }
});