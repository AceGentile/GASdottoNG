@if($supplier->userCan('supplier.modify'))
    <div class="row">
        <div class="col-md-12">
            @include('commons.addingbutton', [
                'template' => 'product.base-edit',
                'typename' => 'product',
                'target_update' => 'product-list-' . $supplier->id,
                'typename_readable' => 'Prodotto',
                'targeturl' => 'products',
                'extra' => ['supplier_id' => $supplier->id]
            ])

            <button type="button" class="btn btn-default" data-toggle="modal" data-target="#importCSV{{ $supplier->id }}">Importa CSV</button>

            <div class="modal fade wizard" id="importCSV{{ $supplier->id }}" tabindex="-1" role="dialog" aria-labelledby="importCSV{{ $supplier->id }}">
                <div class="modal-dialog modal-lg" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                            <h4 class="modal-title">Importa CSV</h4>
                        </div>
                        <div class="wizard_page">
                            <form class="form-horizontal" method="POST" action="{{ url('import/csv?type=products&step=guess') }}" data-toggle="validator" enctype="multipart/form-data">
                                <input type="hidden" name="supplier_id" value="{{ $supplier->id }}" />
                                <div class="modal-body">
                                    <p>
                                        Sono ammessi solo files in formato CSV. Si raccomanda di formattare la propria tabella in modo omogeneo, senza usare celle unite, celle vuote, intestazioni: ogni riga deve contenere tutte le informazioni relative al prodotto. I prezzi vanno espressi senza includere il simbolo dell'euro.
                                    </p>
                                    <p>
                                        Una volta caricato il file sarà possibile specificare quale attributo rappresenta ogni colonna trovata nel documento.
                                    </p>
                                    <p class="text-center">
                                        <img src="{{ url('images/csv_explain.png') }}">
                                    </p>

                                    <hr/>

                                    @include('commons.filefield', [
                                        'obj' => null,
                                        'name' => 'file',
                                        'label' => 'File da Caricare',
                                        'mandatory' => true,
                                        'extra_class' => 'immediate-run',
                                        'extras' => [
                                            'data-url' => 'import/csv?type=products&step=guess',
                                            'data-form-data' => '{"supplier_id": "' . $supplier->id . '"}',
                                            'data-run-callback' => 'wizardLoadPage'
                                        ]
                                    ])
                                </div>

                                <div class="modal-footer">
                                    <button type="button" class="btn btn-default" data-dismiss="modal">Annulla</button>
                                    <button type="submit" class="btn btn-success">Avanti</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="clearfix"></div>
    <hr />
@endif

<div class="row">
    <div class="col-md-12">
        @include('commons.loadablelist', [
            'identifier' => 'product-list-' . $supplier->id,
            'items' => $supplier->all_products,
            'legend' => (object)[
                'class' => 'Product'
            ],
            'filters' => [
                'archived' => (object)[
                    'icon' => 'inbox',
                    'label' => 'Archiviati',
                    'value' => true
                ]
            ]
        ])
    </div>
</div>
