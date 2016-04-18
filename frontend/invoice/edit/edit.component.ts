
import {Component} from 'angular2/core';
import {InvoiceService} from "../invoice.service";
import {Invoice} from "../invoice.model";
import {InvoiceFormComponent} from "../invoice-form.component";
import {RouteParams} from "angular2/router";
import {Router} from "angular2/router";

@Component({
    templateUrl: 'app/invoice/edit/edit.html',
    directives: [InvoiceFormComponent]
})
export class EditInvoiceComponent {
    private invoiceService:InvoiceService;
    private invoice:Invoice;
    private id:number;
    private router:Router;

    constructor(invoiceService:InvoiceService, routeParams:RouteParams, router:Router) {
        this.router = router;
        this.invoiceService = invoiceService;
        this.id = Number(routeParams.get('id'));

        this.invoiceService.getInvoice(this.id).subscribe((invoice:Invoice) => {
            this.invoice = invoice;
        });
    }

    edit():void {
        this.invoiceService.update(this.invoice).subscribe(() => {
            this.router.navigate(['ListInvoices']);
        });
    }

    remove():void {
        if (confirm('Are you sure you want delete the invoice?')) {
            this.invoiceService.remove(this.invoice).subscribe(() => {
                this.router.navigate(['ListInvoices']);
            });
        }
    }
}
