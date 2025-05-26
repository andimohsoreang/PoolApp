<!-- Payment Information Section -->
<div class="card mb-4">
    <div class="card-header">
        <h5 class="card-title mb-0">Informasi Pembayaran</h5>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered">
                <tbody>
                    <tr>
                        <td width="30%">Harga per Jam</td>
                        <td>{{ $paymentDetails['price_per_hour'] }}</td>
                    </tr>
                    <tr>
                        <td>Durasi</td>
                        <td>{{ $paymentDetails['duration_hours'] }}</td>
                    </tr>
                    <tr>
                        <td>Subtotal</td>
                        <td>{{ $paymentDetails['subtotal'] }}</td>
                    </tr>
                    @if($paymentDetails['discount'] != 'Rp 0')
                    <tr>
                        <td>Potongan Diskon</td>
                        <td class="text-danger">- {{ $paymentDetails['discount'] }}</td>
                    </tr>
                    @if($paymentDetails['promo'])
                    <tr>
                        <td>Detail Promo</td>
                        <td>
                            <strong>{{ $paymentDetails['promo']['name'] }}</strong><br>
                            <small class="text-muted">
                                Kode: {{ $paymentDetails['promo']['code'] }}<br>
                                Diskon: {{ $paymentDetails['promo']['discount_type'] === 'percentage' ? $paymentDetails['promo']['discount_value'].'%' : 'Rp '.number_format($paymentDetails['promo']['discount_value'], 0, ',', '.') }}<br>
                                @if($paymentDetails['promo']['max_discount'])
                                Maksimal Diskon: Rp {{ number_format($paymentDetails['promo']['max_discount'], 0, ',', '.') }}
                                @endif
                            </small>
                        </td>
                    </tr>
                    @endif
                    @endif
                    <tr class="table-primary">
                        <td><strong>Total Harga</strong></td>
                        <td><strong>{{ $paymentDetails['total_price'] }}</strong></td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

