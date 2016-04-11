
import {Component} from 'angular2/core';
import {InvoiceService} from "../invoice.service";
import {Invoice} from "../invoice.model";
import {InvoiceFormComponent} from "../invoice-form.component";
import {RouteParams} from "angular2/router";
import {Item} from "../item.model";
import {ROUTER_DIRECTIVES} from "angular2/router";
import {Response} from "angular2/http";

@Component({
    templateUrl: 'app/invoice/view/view.html',
    directives: [ROUTER_DIRECTIVES]
})
export class ViewInvoiceComponent {
    private invoiceService:InvoiceService;
    private invoice:Invoice;
    private id:number;

    constructor(invoiceService:InvoiceService, routeParams:RouteParams) {
        this.invoiceService = invoiceService;
        this.id = Number(routeParams.get('id'));

        this.invoiceService.getInvoice(this.id).subscribe((invoice:Invoice) => {
            this.invoice = invoice;
        });
    }

    edit():void {
        // Todo
    }
}
