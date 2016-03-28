
export class Item {
    public description:string;
    public amount:number;
    public price:number;
    public tax:number;

    public total():number {
        var tax = (this.tax / 100) + 1;

        return (this.amount * this.price) * tax;
    }
}
