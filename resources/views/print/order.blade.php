<!DOCTYPE html>
<html lang="en" dir="">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width,initial-scale=1" />
    <meta http-equiv="X-UA-Compatible" content="ie=edge" />
    <title>Print Order</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css" integrity="sha384-xOolHFLEh07PJGoPkLv1IbcEPTNtaed2xpHsD9ESMhqIYd0nLMwNLD69Npy4HI+N" crossorigin="anonymous">
    <link rel="stylesheet" href="{{ asset('backend/dist-assets/css/plugins/datatables.min.css') }}" />
    <style>
        @media print {
            .card {
                display: block;
            }

            .table td,
            .table th {
                padding: 0.5rem;
                color: black !important;
            }

        }
    </style>
</head>

<body class="text-left">

    <div class="card mb-4">
        <div class="card text-left">
            <div class="card-body">
                <div class="header_img mb-2">
                    <div class="invoice-masthead" style="margin-bottom: 10px;position:relative;">
                        <div class="invoice-text" style="text-align:center; float:none">
                            <h2 class="h2 text-uppercase text-thin mar-no">{{ $order->shop->name }}</h2>
                            <div class="badge badge-dark" style="font-size: 16px;">Tailor & Fabrics</div>
                            <p class="mt-3">{{ $order->shop->branch_name  }} </p>
                            <h4 class="h3 text-uppercase text-thin mar-no text-primary">
                                {{-- <label class="label label-primary">{{ 'INVOICE' }}</label> --}}
                            </h4>
                        </div>

                        <div style="position:absolute;top:20px;">  
                            <img src="{{ asset($order->shop->logo) }}" style="height: 65px; width: 120px;">
                        </div>
                    </div>
                </div>
                <div class="d-flex justify-content-between">
                    <table class="table table-bordered" style="width:30%">
                        <tr>
                            <th colspan="2" class="text-center bg-dark text-white">
                                Customer's Info
                            </th>
                        </tr>
                        <tr>
                            <th>Name:</th>
                            <td>
                                {{ $order->customer->name }}
                            </td>
                        </tr>
                        <tr>
                            <th>Mobile No:</th>
                            <td>
                                {{ $order->customer->phone }}
                            </td>
                        </tr>
                        <tr>
                            <th>Email</th>
                            <td>
                                {{ $order->customer->email }}
                            </td>
                        </tr>
                        <tr>
                            <th>Address</th>
                            <td>
                                {{ $order->customer->address }}
                            </td>
                        </tr>
                    </table>
                    <table class="table table-bordered" style="width:30%">
                        <tr>
                            <th colspan="2" class="text-center bg-dark text-white">
                                Master's Info
                            </th>
                        </tr>
                        <tr>
                            <th>Name:</th>
                            <td>
                                {{ $order->master->name }}
                            </td>
                        </tr>
                        <tr>
                            <th>Mobile No:</th>
                            <td>
                                {{ $order->master->phone }}
                            </td>
                        </tr>
                        <tr>
                            <th>Email</th>
                            <td>
                                {{ $order->master->email }}
                            </td>
                        </tr>                        
                    </table>
                    <table class="table table-bordered" style="width:30%">
                        <tr>
                            <th colspan="2" class="text-center bg-dark text-white">
                                Additional Info
                            </th>
                        </tr>
                        <tr>
                            <th>Order Date:</th>
                            <td>
                                {{ date('d-M-Y',strtotime($order->date)) }}
                            </td>
                        </tr>
                        <tr>
                            <th>Trial Date:</th>
                            <td>
                                {{ date('d-M-Y',strtotime($order->trial_date)) }}
                            </td>
                        </tr>
                        <tr>
                            <th>Delivery Date</th>
                            <td>
                                {{ date('d-M-Y',strtotime($order->delivery_date)) }}
                            </td>
                        </tr>
                    </table>
                </div>
                <div>
                    <table class="table table-bordered">
                        <thead class="bg-dark">
                            <tr class="text-white text-center">
                                <th scope="col">Sl</th>
                                <th scope="col">Item Name</th>
                                <th scope="col">Unit Price</th>
                                <th scope="col">Qunatity</th>
                                <th scope="col">Net Price</th>                          
                            </tr>
                        </thead>
                        <tbody id="table-body">
                            @foreach ($order->order_items as $key => $item)
                                <tr class="text-center">
                                    <th scope="row">{{ $key + 1 }}</th>
                                    <td>{{ ($item->item->name) }}</td>
                                    <td>{{ $item->unit_price }}</td>
                                    <td>{{ $item->qty }}</td>
                                    <td>{{ $item->net_price }}</td>
                                </tr>
                            @endforeach
                        
                        </tbody>
                    </table>

                    @foreach ($order->order_items as $order_item)
                    <div class="d-flex jistify-content-center">
                        <div>{{$order_item->long}}</div>
                    </div>
                    @endforeach

                    <div class="d-flex justify-content-between">
                        <div>
                            <b>Client's Note</b>
                            <ul>
                                @foreach ($c_notes as $c_note)
                                    <li><b>{!!$c_note->note!!}</b></li>
                                @endforeach                           
                            </ul>
                        </div>
                        <div>
                            <b>Master's Note</b>
                            <ul >
                                @foreach ($m_notes as $m_note)
                                    <li>{{$m_note->note}}</li>
                                @endforeach                           
                            
                            </ul>
                        </div>
                        <table class="table table-bordered" style="width:30%">
                            <tr>
                                <th colspan="2" class="text-center bg-dark text-white">
                                    Payment Info
                                </th>
                            </tr>
                            <tr>
                                <th>Sub Total:</th>
                                <td>
                                    {{ $order->sub_total }}
                                </td>
                            </tr>
                            <tr>
                                <th>Discount:</th>
                                <td>
                                    {{ $order->discount }}
                                </td>
                            </tr> 
                            <tr>
                                <th>Grand Total:</th>
                                <td>
                                    {{ $order->grand_total }}
                                </td>
                            </tr> 
                            <tr>
                                <th>Paid:</th>
                                <td>
                                    {{ $order->paid }}
                                </td>
                            </tr> 
                            <tr>
                                <th>Due:</th>
                                <td>
                                    {{ $order->due }}
                                </td>
                            </tr>                          
                        </table>
                    </div>

                </div>
            </div>
        </div>
    </div>


<script src="https://cdn.jsdelivr.net/npm/jquery@3.5.1/dist/jquery.slim.min.js" integrity="sha384-DfXdz2htPH0lsSSs5nCTpuj/zy4C+OGpamoFVy38MVBnE+IbbVYUew+OrCXaRkfj" crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js" integrity="sha384-9/reFTGAW83EW2RDu2S0VKaIzap3H66lZH81PoYlFhbGU+6BZp6G7niu735Sk7lN" crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.min.js" integrity="sha384-+sLIOodYLS7CIrQpBjl+C7nPvqq+FbNUBDunl/OZv93DB7Ln/533i8e/mZXLi/P+" crossorigin="anonymous"></script>
<script>
    $(document).ready(function() {
        window.print();
    });
</script>
</body>

</html>
