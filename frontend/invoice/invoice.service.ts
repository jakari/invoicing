
import {Injectable} from "angular2/core";
import {Invoice} from "./invoice.model";
import {Http} from "angular2/http";
import {Observable} from "rxjs/Observable";
import {Response} from "angular2/http";

@Injectable()
export class InvoiceService {
    private http:Http;

    constructor(http:Http) {
        this.http = http;
    }

    public create(invoice:Invoice):void {
        console.log(this.http.post('/api/invoices', JSON.stringify(invoice)));
    }

    public getInvoices():Observable<Response> {
        return this.http.get('/api/invoices');
    }
}
