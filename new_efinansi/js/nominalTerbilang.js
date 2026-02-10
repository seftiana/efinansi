/**
 * 
 * class nominalTerbilang
 * @description untuk menyebut sebuah angka
 * @returns string
 * @copyright (c) 2016 gamatechno indonesia
 * 
 */

function nominalTerbilang() {

    this.kekata = function (x) {
        var x = Math.floor(x);

        var angka = ["", "satu", "dua", "tiga", "empat", "lima", "enam", "tujuh", "delapan", "sembilan", "sepuluh", "sebelas"];

        var temp = "";

        if (x < 12) {
            temp = " " + angka[x];
        } else if (x < 20) {
            temp = this.kekata(x - 10) + " belas";
        } else if (x < 100) {
            temp = this.kekata(x / 10) + " puluh" + this.kekata(x % 10);
        } else if (x < 200) {
            temp = " seratus" + this.kekata(x - 100);
        } else if (x < 1000) {
            temp = this.kekata(x / 100) + " ratus" + this.kekata(x % 100);
        } else if (x < 2000) {
            temp = " seribu" + this.kekata(x - 1000);
        } else if (x < 1000000) {
            temp = this.kekata(x / 1000) + " ribu" + this.kekata(x % 1000);
        } else if (x < 1000000000) {
            temp = this.kekata(x / 1000000) + " juta" + this.kekata(x % 1000000);
        } else if (x < 1000000000000) {
            // temp = this.kekata(x / 1000000000) + " milyar" + this.kekata(Math.fmod(x, 1000000000));
            temp = this.kekata(x / 1000000000) + " milyar" + this.kekata( x % 1000000000);
        } else if (x < 1000000000000000) {
            // temp = this.kekata(x / 1000000000000) + " trilyun" + this.kekata(Math.fmod(x, 1000000000000));
            temp = this.kekata(x / 1000000000000) + " trilyun" + this.kekata( x % 1000000000000);
        }

        return temp;
    }

    this.Num2WordInd = function (x, style) {
        var hasil = null;
        style = typeof style !== 'undefined' ? style : 4;
        if (x < 0) {
            hasil = "minus ".this.kekata(x).trim();
        } else {
            hasil = this.kekata(x).trim();
        }

        switch (style) {
            case 1:
                hasil = hasil.toUpperCase();
                break;
            case 2:
                hasil = hasil.toLowerCase();
                break;
            case 3:
                hasil =  hasil.ucwords();
                break;
            default:
                hasil =  hasil.ucwords();
                break;
        }

        return hasil;
    }


    this.Terbilang = function (number, format) {
        return this.Num2WordInd(number, format);
    }



    //membuat huruf besar kata pertama
    String.prototype.ucwords = function () {
        str = this.toLowerCase();
        return str.replace(/(^([a-zA-Z\p{M}]))|([ -][a-zA-Z\p{M}])/g,
                function ($1) {
                    return $1.toUpperCase();
                });
    }
}