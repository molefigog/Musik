<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CPay Checkout</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body class="bg-light">
    <div class="container py-5">
        <div class="card shadow border-0 mx-auto" style="max-width: 520px;">
            <div class="card-body p-4">
                <h3 class="text-center mb-4">Secure Payment</h3>

                @if (!empty($message))
                    <div class="alert alert-info">{!! $message !!}</div>
                @endif

                <form method="post">
                    <input type="hidden" name="payment_type" id="payment_type" value="mobile">

                    <div class="row g-3 mb-4">
                        <div class="col-6">
                            <div class="card payment-option active" id="mobileOption" onclick="selectPayment('mobile')">
                                <div class="card-body text-center">
                                    <h4>📱</h4>
                                    <strong>Mobile Money</strong>
                                    <small class="d-block text-muted">OTP confirmation</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="card payment-option" id="cardOption" onclick="selectPayment('card')">
                                <div class="card-body text-center">
                                    <h4>💳</h4>
                                    <strong>Card</strong>
                                    <small class="d-block text-muted">Visa / Mastercard</small>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Mobile Number</label>
                        <input class="form-control form-control-lg" name="msisdn" placeholder="266XXXXXXXX" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Amount</label>
                        <input class="form-control form-control-lg" type="number" step="0.01" name="amount"
                            required>
                    </div>

                    <div id="descriptionBox">
                        <div class="mb-3">
                            <label class="form-label">Description</label>
                            <input class="form-control" name="description" value="Payment">
                        </div>
                    </div>

                    <div id="emailBox" style="display:none">
                        <div class="mb-3">
                            <label class="form-label">Email</label>
                            <input class="form-control" type="email" name="email" placeholder="customer@email.com">
                        </div>
                    </div>

                    <button type="submit" name="pay" class="btn btn-primary btn-lg w-100">Continue Payment</button>
                </form>
            </div>
        </div>
    </div>

    @if (!empty($cardHtml))
        <div id="cardPaymentContainer" class="container mt-4"></div>
    @endif

    @if (!empty($showModal))
        <div class="modal fade" id="otpModal" tabindex="-1" aria-modal="true" role="dialog">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <form method="post">
                        <input type="hidden" name="transaction_id" value="{{ $payment['transaction_id'] ?? '' }}">
                        <input type="hidden" name="payment_msisdn" value="{{ $payment['msisdn'] ?? '' }}">
                        <input type="hidden" name="payment_amount" value="{{ $payment['amount'] ?? '' }}">
                        <div class="modal-header">
                            <h5 class="modal-title">Confirm Payment</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <label class="mb-2">Enter SMS OTP</label>
                            <input class="form-control form-control-lg text-center" name="otp" required>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" name="confirm" class="btn btn-success">Confirm Payment</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        const cardPaymentContainer = document.getElementById('cardPaymentContainer');
        if (cardPaymentContainer) {
            const rawHtml = @json($cardHtml);
            if (rawHtml) {
                cardPaymentContainer.innerHTML = rawHtml;
            }
        }

        function selectPayment(type) {
            document.getElementById('payment_type').value = type;
            document.getElementById('mobileOption').classList.remove('active');
            document.getElementById('cardOption').classList.remove('active');

            if (type === 'card') {
                document.getElementById('cardOption').classList.add('active');
                document.getElementById('emailBox').style.display = 'block';
                document.getElementById('descriptionBox').style.display = 'none';
            } else {
                document.getElementById('mobileOption').classList.add('active');
                document.getElementById('emailBox').style.display = 'none';
                document.getElementById('descriptionBox').style.display = 'block';
            }
        }

        @if (!empty($showModal))
            document.addEventListener('DOMContentLoaded', function() {
                const otpModal = document.getElementById('otpModal');
                if (otpModal) {
                    const modal = new bootstrap.Modal(otpModal, {
                        backdrop: 'static',
                        keyboard: false
                    });
                    modal.show();
                }
            });
        @endif
    </script>
</body>

</html>
