class PaymentStatusChecker {
    constructor(donationId, snapToken, options = {}) {
        this.donationId = donationId;
        this.snapToken  = snapToken;
        this.options    = {
            onSuccess:  options.onSuccess  || (() => {}),
            onExpired:  options.onExpired  || (() => {}),
            onChecking: options.onChecking || (() => {}),
        };
        this.attempts   = 0;
        this.isChecking = false;
        this.timeoutId  = null;
        this.stopped    = false;
    }

    getDelay(n) {
        if (n <= 3)  return 5000;
        if (n <= 6)  return 10000;
        if (n <= 10) return 20000;
        return 30000;
    }

    start() {
        this.stopped = false;
        this.doCheck();
    }

    stop() {
        this.stopped = true;
        if (this.timeoutId) { clearTimeout(this.timeoutId); this.timeoutId = null; }
    }

    scheduleNext() {
        if (this.stopped) return;
        const delay = this.getDelay(this.attempts);
        this.timeoutId = setTimeout(() => this.doCheck(), delay);
    }

    async doCheck() {
        if (this.stopped || this.isChecking) return;
        this.isChecking = true;
        this.attempts++;

        const nextDelay = this.getDelay(this.attempts);
        this.options.onChecking(this.attempts, nextDelay);

        try {
            const response = await fetch(`/donations/check-status/${this.snapToken}`);
            const data     = await response.json();

            if (data.success && data.data) {
                const status = data.data.status;
                if (status === 'PAID') {
                    this.stop();
                    this.options.onSuccess(data.data);
                    setTimeout(() => window.location.reload(), 1000);
                    return;
                }
                if (status === 'EXPIRED' || status === 'FAILED') {
                    this.stop();
                    this.options.onExpired(data.data);
                    setTimeout(() => window.location.reload(), 2000);
                    return;
                }
            }
        } catch (e) {
            console.error('Error checking payment status:', e);
        } finally {
            this.isChecking = false;
        }

        this.scheduleNext();
    }
}

document.addEventListener('DOMContentLoaded', function() {
    if (typeof window.paymentConfig === 'undefined') return;

    const checker = new PaymentStatusChecker(
        window.paymentConfig.donationId,
        window.paymentConfig.snapToken,
        {
            onChecking: function(attempts, nextDelay) {
                const el = document.getElementById('status-check-info');
                if (el) el.innerHTML = `
                    <small class="text-muted">
                        <i class="fa fa-sync-alt fa-spin me-1"></i>
                        Cek berikutnya dalam ${nextDelay / 1000} detik (percobaan ke-${attempts})
                    </small>`;
            },
            onSuccess: function() {
                const el = document.getElementById('status-check-info');
                if (el) el.innerHTML = `
                    <div class="alert alert-success text-center">
                        <i class="fa fa-check-circle me-2"></i> Pembayaran berhasil! Mengalihkan...
                    </div>`;
            },
            onExpired: function() {
                const el = document.getElementById('status-check-info');
                if (el) el.innerHTML = `
                    <div class="alert alert-danger text-center">
                        <i class="fa fa-times-circle me-2"></i> Waktu pembayaran habis.
                    </div>`;
            }
        }
    );

    checker.start();

    window.addEventListener('beforeunload', () => checker.stop());
});