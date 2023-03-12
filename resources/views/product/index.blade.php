@include('shared/header')
    <div class="container-fluid">
      <div class="row">
      @include('shared/navigation')

        <main role="main" class="col-md-9 ml-sm-auto col-lg-10 pt-3 px-4">

          <h2>List Products</h2><h4>Total products : {{$totalproducts}}</h4>
          @if ($message = Session::get('success'))
            <div class="alert alert-success">
                <p>{{ $message }}</p>
            </div>
        @endif
          <div class="table-responsive">
            <table class="table table-striped table-sm">
            <thead>
                <tr>
                    <th>Image</th>
                    <th>Start Traking</th>
                    <th>Title</th>
                    <th>Prix</th>
                    <th>Todaysales</th>
                    <th>2</th>
                    <th>3</th>
                    <th>4</th>
                    <th>5</th>
                    <th>6</th>
                    <th>7</th>
                    <th>Weeklysales</th>
                    <th>Monthlysales</th>
                    <th>Totalsales</th>
                    <th>Revenue</th>
                    <th>Favoris</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($products as $product)
                    <tr>
                        <td><a href="{{ $product->url }}" target="_blank"><img src="{{ $product->imageproduct }}" width="100" height="100"></a></td>
                        <td>{{ $product->created_at }}</td>
                        <td><a href="{{ $product->url }}" target="_blank">{{ $product->title }}</a></td>
                        <td>{{ $product->prix }} $</td>
                        <td>{{ $product->todaysales }}</td>
                        <td>{{ $product->sales2 }}</td>
                        <td>{{ $product->sales3 }}</td>
                        <td>{{ $product->sales4 }}</td>
                        <td>{{ $product->sales5 }}</td>
                        <td>{{ $product->sales6 }}</td>
                        <td>{{ $product->sales7 }}</td>
                        <td>{{ $product->weeklysales }}</td>
                        <td>{{ $product->monthlysales }}</td>
                        <td>{{ $product->totalsales }}</td>
                        <td>{{ $product->revenue }} $</td>
                        <td>{{ $product->favoris }}</td>

                    </tr>
                    @endforeach
            </tbody>
            </table>
          </div>
          <div>
        {{ $products->links() }}


        </div>
        </main>
      </div>
    </div>
    
    @include('shared/footer')
