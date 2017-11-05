<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8" />
        <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1" />
        <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0">

        <title>GASdotto</title>

        <?php

        $css = [
            '/css/bootstrap.min.css',
            '/css/bootstrap-datepicker3.min.css',
            '/css/bootstrap-multiselect.css',
            '/css/bootstrap-table.css',
            '/css/bootstrap-toggle.css',
            '/css/bootstrap.vertical-tabs.css',
            '/css/jquery-ui.css',
            '/css/chartist.css',
            '/css/gasdotto.css'
        ];

        ?>

        @if(env('APP_DEBUG') == true)
            @foreach($css as $c)
                <link rel="stylesheet" type="text/css" href="{{ url($c) }}">
            @endforeach
        @else
            {!! Minify::stylesheet($css)->withFullUrl() !!}
        @endif

        <meta name="csrf-token" content="{{ csrf_token() }}"/>
    </head>
    <body>
        <div id="preloader">
            <img src="{{ asset('images/loading.svg') }}">
        </div>

        <nav class="navbar navbar-default navbar-fixed-top navbar-inverse">
            <div class="container-fluid">
                <div class="navbar-header">
                    <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#bs-example-navbar-collapse-1" aria-expanded="false">
                        <span class="sr-only">Toggle navigation</span>
                        <span class="icon-bar"></span>
                        <span class="icon-bar"></span>
                        <span class="icon-bar"></span>
                    </button>
                    <a class="navbar-brand hidden-md" href="{{ url('/') }}">GASdotto</a>
                </div>

                <div class="collapse navbar-collapse" id="bs-example-navbar-collapse-1">
                    @if(isset($menu))
                        {!! $menu !!}
                    @endif

                    @if(Auth::check())
                        <ul class="nav navbar-nav navbar-right">
                            <li>
                                <a href="#" data-toggle="modal" data-target="#feedback-modal"><span class="glyphicon glyphicon-bullhorn" aria-hidden="true"></span></a>
                            </li>
                            <li id="help-trigger">
                                <a href="#"><span class="glyphicon glyphicon-question-sign" aria-hidden="true"></span></a>
                            </li>
                            <li>
                                <a href="{{ url('logout') }}" onclick="event.preventDefault(); document.getElementById('logout-form').submit();"><span class="glyphicon glyphicon-off" aria-hidden="true"></span></a>
                                <form id="logout-form" action="{{ url('logout') }}" method="POST" style="display: none;">
                                    {{ csrf_field() }}
                                </form>
                            </li>
                        </ul>
                    @endif
                </div>
            </div>
        </nav>

        <div class="container-fluid">
            <div class="row">
                <div class="col-md-12" id="main-contents">
                    @include('commons.flashing')
                    @yield('content')
                </div>
            </div>
        </div>

        <div id="postponed"></div>
        <div id="bottom-stop"></div>

        <div class="modal fade" id="feedback-modal" tabindex="-1" role="dialog">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                        <h4 class="modal-title">Feedback</h4>
                    </div>
                    <form class="form-horizontal inner-form" method="POST" action="http://vh.madbob.org/vh.js.php">
                        <input type="hidden" name="project" value="madbob/gasdottong">
                        <input type="hidden" name="close-modal" value="1">

                        <div class="modal-body">
                            <p>
                                Vuoi inviare una segnalazione su GASdotto? Fallo qui!
                            </p>
                            <p>
                                Attenzione: per problemi sui contenuti di questo sito (fornitori, ordini, prenotazioni...) fai riferimento agli amministrazioni del tuo GAS. I dati inviati per mezzo di questo pannello saranno pubblici: non immettere informazioni e contatti personali!
                            </p>

                            <hr/>

                            @include('commons.textfield', [
                                'obj' => null,
                                'name' => 'title',
                                'label' => 'Titolo',
                                'mandatory' => true,
                            ])

                            @include('commons.textarea', [
                                'obj' => null,
                                'name' => 'contents',
                                'label' => 'Contenuto',
                                'mandatory' => true,
                            ])
                        </div>

                        <div class="modal-footer">
                            <button type="button" class="btn btn-default" data-dismiss="modal">Annulla</button>
                            <button type="submit" class="btn btn-success">Invia</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <?php

        $scripts = [
            '/js/jquery-2.1.1.js',
            '/js/jquery-ui.js',
            '/js/bootstrap.min.js',
            '/js/bootstrap-datepicker.min.js',
            '/js/bootstrap-datepicker.it.min.js',
            '/js/bootstrap-multiselect.js',
            '/js/jquery.mjs.nestedSortable.js',
            '/js/typeahead.bundle.js',
            '/js/validator.min.js',
            '/js/jquery.fileupload.js',
            '/js/bootstrap-table.js',
            '/js/bootstrap-table-it-IT.js',
            '/js/bootstrap-toggle.js',
            '/js/marked.min.js',
            '/js/chartist.js',
            '/js/jquery.TableCSVExport.js',

            '/js/utils.js',
            '/js/inline-help.js',
            '/js/jquery.manyrows.js',
            '/js/jquery.dynamictree.js',
            '/js/gasdotto.js'
        ];

        ?>

        @if(env('APP_DEBUG') == true)
            @foreach($scripts as $s)
                <script type="application/javascript" src="{{ url($s) }}"></script>
            @endforeach
        @else
            {!! Minify::javascript($scripts)->withFullUrl() !!}
        @endif
    </body>
</html>
