
import {Component} from 'angular2/core';
import {InvoiceService} from "../invoice.service";
import {Invoice} from "../invoice.model";
import {CustomerModel} from "../customer.model";
import {InvoiceFormComponent} from "../invoice-form.component";
import {Observable} from "rxjs/Observable";
import {Router} from "angular2/router";

@Component({
    templateUrl: 'app/invoice/create/create.html',
    directives: [InvoiceFormComponent]
})
export class CreateInvoiceComponent {
    private invoiceService:InvoiceService;
    private invoice:Invoice;
    private customerSearch:String;
    private router:Router;

    constructor(invoiceService:InvoiceService, router:Router) {
        this.invoiceService = invoiceService;
        this.invoice = new Invoice();
        this.invoice.customer = new CustomerModel();
        this.router = router;
    }

    create():void {
        this.invoiceService.create(this.invoice).subscribe(() => {
            this.router.navigate(['ListInvoices']);
        });
    }
}
