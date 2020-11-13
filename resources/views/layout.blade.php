<!doctype html>
<html lang="en" class="h-100">
  <head>
    @include('includes._head')

    @stack('styles')
  </head>
  <body class="d-flex flex-column h-100">
    <header>
      @include('includes._header')
    </header>

    <!-- Begin page content -->
    <main role="main" class="flex-shrink-0">
      <div class="container"> 
        <div class="row mt-3">
          <div class="col-8">       
            @yield('content')
          </div>
          <div class="col-4">       
            @section('sidebar')
              <h2>&nbsp;</h2>
            @show
          </div>
        </div>
      </div>
    </main>
    
    @include('includes._footer')

    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js" integrity="sha384-DfXdz2htPH0lsSSs5nCTpuj/zy4C+OGpamoFVy38MVBnE+IbbVYUew+OrCXaRkfj" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js" integrity="sha384-9/reFTGAW83EW2RDu2S0VKaIzap3H66lZH81PoYlFhbGU+6BZp6G7niu735Sk7lN" crossorigin="anonymous"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js" integrity="sha384-B4gt1jrGC7Jh4AgTPSdUtOBvfO8shuf57BaghqFfPlYxofvL8/KUEfYiJOMMV+rV" crossorigin="anonymous"></script>

    @stack('scripts')
  </body>
</html>