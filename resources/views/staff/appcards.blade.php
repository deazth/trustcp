<html>
  <head>
  <style>
  img {
    border: 2px solid #555;
  }

  .container {
    position: relative;
    text-align: center;
    width:600px;
    margin: auto;
  }

  /* From */
  .fromsender {
    position: absolute;
    bottom: 23px;
    left: 115px;
    font-family: Brush Script MT, Verdana, sans-serif;
    font-weight: bold;
    color: #FFF;
  }

  /* Dear to */
  .toreceiver {
    position: absolute;
    top: 23px;
    left: 110px;
    font-family: Brush Script MT, Verdana, sans-serif;
    font-weight: bold;
    color: {{ $card->namecolor() }};
  }


  /* The content */
  .content-block {
    position: absolute;
    width: 250px;
    top: 45%;
    right: 10px;
    background-color: #666;
    color: #FFF;
    padding-left: 20px;
    padding-right: 20px;
    transform: translate(0px, -50%);
    font-family: Brush Script MT, Verdana, sans-serif;
    border-radius: 10px;
    line-height: 1.4;
  }

  </style>
  </head>
  <body>
    <div class="container">
      <img src="{{ url('img/postcard/' . $card->template . '_front_2.png') }}" alt="kad_depan" style="width:100%;">
    </div>
    <br />
    <div class="container">
      <img src="{{ url('img/postcard/' . $card->template . '_back_2.png') }}" alt="kad_belakang" style="width:100%;">
      <div class="toreceiver">{{ $card->recipient->name }}</div>
      <div class="fromsender">{{ $card->sender->name }}</div>
      <div class="content-block">
        <p>{{ $card->content }}</p>
      </div>
    </div>
  </body>
</html>
