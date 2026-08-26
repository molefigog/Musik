@extends('layouts.master')
@section('content')
    <div class="container d-flex justify-content-center align-items-start" style="height: 100vh; overflow-y: auto;">
        <div class="row text-center w-100">
            <h6 class="text-muted w-100">OpenApi Testing php</h6>
            <div class="col-12 col-sm-6 col-md-3 mb-4">
                <div class="card" style="height: 100%; display: flex; flex-direction: column;">
                    <div class="card-body" style="flex-grow: 1; overflow-y: auto;">
                        <button class="btn btn-info btn-sm">C2B LSL1,00</button>
                        <form id="chargeForm" action="{{ url('api/charge') }}" method="get">
                            @csrf
                            <input type="hidden" value="1" id="input_Amount" name="input_Amount" required>
                            <div class="mb-3">
                                <label for="input_CustomerMSISDN" class="form-label">MSISDN:</label>
                                <input type="text" class="form-control" id="input_CustomerMSISDN"
                                    name="input_CustomerMSISDN" pattern="5\d{7}" placeholder="Enter mpesa number"
                                    title="Please enter 8 digits starting with 5" maxlength="8" required>
                                <div id="msisdnError" style="color: red; display: none;">MSISDN must be exactly 8 digits
                                    long and start with 5.</div>
                            </div>

                            <div class="mb-3">
                                <input type="hidden" class="form-control" id="input_PurchasedItemsDesc"
                                    name="input_PurchasedItemsDesc" value="Track 1" required>
                            </div>

                            <button class="btn btn-primary w-100" type="submit" id="submitButton">
                                <span id="buttonText">Pay LSL1,00</span>
                                <span id="spinner" class="spinner-border spinner-border-sm" role="status"
                                    aria-hidden="true" style="display: none;"></span>
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Second Card -->
            <div class="col-12 col-sm-6 col-md-3 mb-4">
                <div class="card" style="height: 100%; display: flex; flex-direction: column;">
                    <div class="card-body" style="flex-grow: 1; overflow-y: auto;">
                        <button class="btn btn-info btn-sm">B2C LSL1,00</button>
                        <form id="chargeForm2" action="{{ url('api/b2c') }}" method="get">
                            @csrf
                            <input type="hidden" value="1" id="input_Amount" name="input_Amount" required>

                            <div class="mb-3">
                                <label for="input_CustomerMSISDN" class="form-label">MSISDN:</label>
                                <input type="text" class="form-control" id="input_CustomerMSISDN"
                                    name="input_CustomerMSISDN" pattern="5\d{7}" placeholder="Enter mpesa number"
                                    title="Please enter 8 digits starting with 5" maxlength="8" required>
                                <div id="msisdnError" style="color: red; display: none;">MSISDN must be exactly 8 digits
                                    long and start with 5.</div>
                            </div>

                            <div class="mb-3">
                                <input type="hidden" class="form-control" id="input_PaymentItemsDesc"
                                    name="input_PaymentItemsDesc" value="Salary payment" required>
                            </div>

                            <button class="btn btn-primary w-100" type="submit" id="submitButton2">
                                <span id="buttonText2">Withdraw LSL1,00</span>
                                <span id="spinner2" class="spinner-border spinner-border-sm" role="status"
                                    aria-hidden="true" style="display: none;"></span>
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Third Card -->
            <div class="col-12 col-sm-6 col-md-3 mb-4">
                <div class="card" style="height: 100%; display: flex; flex-direction: column;">
                    <div class="card-body" style="flex-grow: 1; overflow-y: auto;">
                        <button class="btn btn-info btn-sm">B2B LSL1,00</button>
                        <form id="chargeForm3" action="{{ url('api/b2b') }}" method="get">
                            @csrf
                            <input type="hidden" value="1" id="input_Amount" name="input_Amount" required>

                            <div class="mb-3">
                                <label for="input_ReceiverPartyCode" class="form-label">RECEIVER SC:</label>
                                <input type="text" class="form-control" id="input_ReceiverPartyCode"
                                    name="input_ReceiverPartyCode" placeholder="Enter mpesa number"
                                    title="Please enter 5 digits" required>
                                <div id="msisdnError" style="color: red; display: none;">6digit shortcode</div>
                            </div>

                            <div class="mb-3">
                                <input type="hidden" class="form-control" id="input_PurchasedItemsDesc"
                                    name="input_PurchasedItemsDesc" value="shoes" required>
                            </div>

                            <button class="btn btn-primary w-100" type="submit" id="submitButton3">
                                <span id="buttonText2">Pay LSL1,00</span>
                                <span id="spinner3" class="spinner-border spinner-border-sm" role="status"
                                    aria-hidden="true" style="display: none;"></span>
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Reverse Card -->
            <div class="col-12 col-sm-6 col-md-3 mb-4">
                <div class="card" style="height: 100%; display: flex; flex-direction: column;">
                    <div class="card-body" style="flex-grow: 1; overflow-y: auto;">
                        <button class="btn btn-info btn-sm">REVERSE LSL1,00</button>
                        <form id="reverseForm" action="{{ url('api/reverse') }}" method="put">
                            @csrf
                            <input type="hidden" value="1" id="input_Amount" name="input_Amount" required>

                            <div class="mb-3">
                                <label for="input_TransactionID" class="form-label">REVERSE:</label>
                                <input type="text" class="form-control" id="input_TransactionID"
                                    name="input_TransactionID" placeholder="Enter mpesa number"
                                    title="Please enter 5 digits" value="0000000000001" required>
                            </div>

                            <button class="btn btn-primary w-100" type="submit" id="submitButtonReverse">
                                <span id="buttonText2">Pay LSL1,00</span>
                                <span id="spinnerReverse" class="spinner-border spinner-border-sm" role="status"
                                    aria-hidden="true" style="display: none;"></span>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>


    <!-- Bootstrap Modal -->
    <div class="modal fade" id="responseModal" tabindex="-1" aria-labelledby="responseModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="responseModalLabel">Transaction Response</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p id="modalMessage">Processing...</p>
                </div>
            </div>
        </div>
    </div>
@endsection
@push('pesa')
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            function handleSubmit(formId, buttonId, spinnerId, modalMessageId) {
                const form = document.getElementById(formId);
                form.addEventListener("submit", function(event) {
                    event.preventDefault(); // Prevent page reload

                    const submitButton = document.getElementById(buttonId);
                    const spinner = document.getElementById(spinnerId);
                    const modalMessage = document.getElementById(modalMessageId);

                    // Disable button & show spinner
                    submitButton.disabled = true;
                    spinner.style.display = "inline-block";

                    // Get form data and convert it to a query string
                    const formData = new FormData(form);
                    const queryString = new URLSearchParams(formData).toString();
                    const url = form.action + "?" + queryString; // Append parameters to URL

                    fetch(url, {
                            method: "GET",
                            headers: {
                                "X-Requested-With": "XMLHttpRequest",
                            }
                        })
                        .then(response => response.json())
                        .then(data => {
                            modalMessage.innerHTML = `<pre>${JSON.stringify(data, null, 2)}</pre>`;
                            var modal = new bootstrap.Modal(document.getElementById('responseModal'));
                            modal.show();
                        })
                        .catch(error => {
                            modalMessage.innerHTML =
                                `<p style="color: red;">Error: ${error.message}</p>`;
                            var modal = new bootstrap.Modal(document.getElementById('responseModal'));
                            modal.show();
                        })
                        .finally(() => {
                            // Re-enable button & hide spinner
                            submitButton.disabled = false;
                            spinner.style.display = "none";
                        });
                });
            }

            // Attach event listeners to each form
            handleSubmit("chargeForm", "submitButton", "spinner", "modalMessage");
            handleSubmit("chargeForm2", "submitButton2", "spinner2", "modalMessage");
            handleSubmit("chargeForm3", "submitButton3", "spinner3", "modalMessage");
            handleSubmit("chargeForm4", "submitButton4", "spinner4", "modalMessage");
        });
        document.addEventListener("DOMContentLoaded", function() {
            const reverseForm = document.getElementById("reverseForm");

            reverseForm.addEventListener("submit", function(event) {
                event.preventDefault(); // Prevent page reload

                const submitButton = document.getElementById("submitButtonReverse");
                const spinner = document.getElementById("spinnerReverse");
                const modalMessage = document.getElementById("modalMessage");

                // Disable button & show spinner
                submitButton.disabled = true;
                spinner.style.display = "inline-block";

                // Get form data
                const formData = new FormData(reverseForm);
                const formObj = {};
                formData.forEach((value, key) => {
                    formObj[key] = value
                });

                // Send POST request with form data
                fetch(reverseForm.action, {
                        method: "put",
                        headers: {
                            "X-Requested-With": "XMLHttpRequest",
                            "Content-Type": "application/json",
                            "Accept": "application/json",
                            "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]')
                                .getAttribute("content")
                        },
                        body: JSON.stringify(formObj)
                    })
                    .then(response => response.json())
                    .then(data => {
                        modalMessage.innerHTML = `<pre>${JSON.stringify(data, null, 2)}</pre>`;
                        var modal = new bootstrap.Modal(document.getElementById('responseModal'));
                        modal.show();
                    })
                    .catch(error => {
                        modalMessage.innerHTML = `<p style="color: red;">Error: ${error.message}</p>`;
                        var modal = new bootstrap.Modal(document.getElementById('responseModal'));
                        modal.show();
                    })
                    .finally(() => {
                        // Re-enable button & hide spinner
                        submitButton.disabled = false;
                        spinner.style.display = "none";
                    });
            });
        });
    </script>
@endpush
